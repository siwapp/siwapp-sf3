<?php

namespace Siwapp\RecurringInvoiceBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\CoreBundle\Entity\Item;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

/**
 * @Route("/recurring")
 */
class RecurringInvoicesController extends Controller
{
    /**
     * @Route("/", name="recurring_index")
     * @Template("SiwappRecurringInvoiceBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice')->createQueryBuilder('ri');

        $form = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\SearchRecurringInvoiceType', null, [
            'action' => $this->generateUrl('recurring_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->applySearchFilters($qb, $form->getData());
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            // @todo Unhardcode this.
            50
        );


        $listForm = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\RecurringInvoiceListType', $pagination->getItems(), [
            'action' => $this->generateUrl('recurring_index'),
        ]);
        $listForm->handleRequest($request);
        if ($listForm->isValid()) {
            $data = $listForm->getData();
            if ($request->request->has('delete')) {
                foreach ($data['recurring_invoices'] as $recurring) {
                    $em->remove($recurring);
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Recurring invoice(s) deleted.');

                // Rebuild the query, since some objects are now missing.
                return $this->redirect($this->generateUrl('recurring_index'));
            }
        }

        $pending = 0;
        foreach ($pagination as $recurring) {
            $pending += $recurring->countPendingInvoices($recurring);
        }
        if ($pending) {
            $this->get('session')->getFlashBag()
                ->add('warning', sprintf('There are %d recurring invoices that were not executed.', $pending));
        }

        return array(
            'invoices' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
            'expected' => $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice')->getAverageDayAmount(),
        );
    }

    /**
     * @Route("/{id}/show", name="recurring_show")
     * @Template("SiwappRecurringInvoiceBundle:Default:show.html.twig")
     */
    public function showAction($id)
    {
        // No show for now, always redirect to edit.
        return $this->redirect($this->generateUrl('estimate_edit', ['id' => $id]));
    }

    /**
     * @Route("/add", name="recurring_add")
     * @Template("SiwappRecurringInvoiceBundle:Default:edit.html.twig")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new RecurringInvoice();
        $invoice->addItem(new Item());

        $form = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\RecurringInvoiceType', $invoice, [
            'action' => $this->generateUrl('recurring_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($invoice);
            $em->flush();

            return $this->redirect($this->generateUrl('recurring_edit', array('id' => $invoice->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/{id}/edit", name="recurring_edit")
     * @Template("SiwappRecurringInvoiceBundle:Default:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice')->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Recurring Invoice entity.');
        }
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\RecurringInvoiceType', $invoice, [
            'action' => $this->generateUrl('recurring_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($invoice);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Recurring invoice was updated.');
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/delete", name="recurring_generate_pending")
     * @Method({"POST"})
     */
    public function generatePendingAction()
    {
        $count = $this->get('siwapp_recurring_invoice.invoice_generator')->generateAll();
        $this->get('session')->getFlashBag()->add('success', sprintf('%d invoices were generated.', $count));

        return $this->redirect($this->generateUrl('recurring_index'));
    }

    /**
     * @Route("/delete", name="recurring_delete")
     */
    public function deleteAction()
    {
        return $this->redirect($this->generateUrl('recurring_index'));
    }

    protected function applySearchFilters(QueryBuilder $qb, array $data)
    {
        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }
            if ($field == 'terms') {
                $qb->join('ri.serie', 's', 'WITH', 'ri.serie = s.id');
                $terms = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('s.name', $terms)
                ));
            } elseif ($field == 'status') {
                $qb->andWhere('ri.status = :status');
                $qb->setParameter('status', $value);
            } elseif ($field == 'customer') {
                $customer = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('ri.customer_name', $customer),
                    $qb->expr()->like('ri.customer_identification', $customer)
                ));
            } elseif ($field == 'serie') {
                $qb->andWhere('ri.serie = :series');
                $qb->setParameter('series', $value);
            }
        }
    }
}
