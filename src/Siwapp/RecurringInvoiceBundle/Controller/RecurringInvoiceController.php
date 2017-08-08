<?php

namespace Siwapp\RecurringInvoiceBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\CoreBundle\Controller\AbstractInvoiceController;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

/**
 * @Route("/recurring")
 */
class RecurringInvoiceController extends AbstractInvoiceController
{
    /**
     * @Route("", name="recurring_index")
     * @Template("SiwappRecurringInvoiceBundle:RecurringInvoice:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice');
        $repo->setPaginator($this->get('knp_paginator'));
        // @todo Unhardcode this.
        $limit = 50;

        $form = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\SearchRecurringInvoiceType', null, [
            'action' => $this->generateUrl('recurring_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pagination = $repo->paginatedSearch($form->getData(), $limit, $request->query->getInt('page', 1));
        } else {
            $pagination = $repo->paginatedSearch([], $limit, $request->query->getInt('page', 1));
        }

        $listForm = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\RecurringInvoiceListType', $pagination->getItems(), [
            'action' => $this->generateUrl('recurring_index'),
        ]);
        $listForm->handleRequest($request);
        if ($listForm->isSubmitted() && $listForm->isValid()) {
            $data = $listForm->getData();
            if ($request->request->has('delete')) {
                if (empty($data['recurring_invoices'])) {
                    $this->addTranslatedMessage('flash.nothing_selected', [], 'warning');
                }
                else {
                    foreach ($data['recurring_invoices'] as $recurring) {
                        $em->remove($recurring);
                    }
                    $em->flush();
                    $this->addTranslatedMessage('flash.bulk_deleted');

                    // Rebuild the query, since some objects are now missing.
                    return $this->redirect($this->generateUrl('recurring_index'));
                }
            }
        }

        $pending = 0;
        foreach ($pagination as $recurring) {
            $pending += $recurring->countPendingInvoices($recurring);
        }
        if ($pending) {
            $this->addTranslatedMessage('flash.invoices_pending', ['%count%' => $pending], 'warning');
        }

        return array(
            'invoices' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
            'expected' => $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice')->getAverageDayAmount(),
            'pending_num' => $pending,
        );
    }

    /**
     * @Route("/add", name="recurring_add")
     * @Template("SiwappRecurringInvoiceBundle:RecurringInvoice:edit.html.twig")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new RecurringInvoice();
        $newItem = new Item($em->getRepository('SiwappCoreBundle:Tax')->findBy(['is_default' => 1]));
        $invoice->addItem($newItem);
        $terms = $em->getRepository('SiwappConfigBundle:Property')->get('legal_terms');
        if ($terms) {
            $invoice->setTerms($terms);
        }

        $form = $this->createForm('Siwapp\RecurringInvoiceBundle\Form\RecurringInvoiceType', $invoice, [
            'action' => $this->generateUrl('recurring_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($invoice);
            $em->flush();
            $this->addTranslatedMessage('flash.added');

            return $this->redirect($this->generateUrl('recurring_edit', array('id' => $invoice->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
        );
    }

    /**
     * @Route("/{id}/edit", name="recurring_edit")
     * @Template("SiwappRecurringInvoiceBundle:RecurringInvoice:edit.html.twig")
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($invoice);
            $em->flush();
            $this->addTranslatedMessage('flash.updated');
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
        );
    }

    /**
     * @Route("/delete", name="recurring_generate_pending")
     * @Method({"POST"})
     */
    public function generatePendingAction()
    {
        $count = $this->get('siwapp_recurring_invoice.invoice_generator')->generateAll();
        $this->addTranslatedMessage('flash.invoices_generated', ['%count%' => $count]);

        return $this->redirect($this->generateUrl('recurring_index'));
    }

    /**
     * @Route("/{id}/delete", name="recurring_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $recurring = $em->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice')->find($id);
        if (!$recurring) {
            throw $this->createNotFoundException('Unable to find Recurring invoice entity.');
        }
        $em->remove($recurring);
        $em->flush();
        $this->addTranslatedMessage('flash.deleted');

        return $this->redirect($this->generateUrl('recurring_index'));
    }

    /**
     * @Route("/form-totals", name="recurring_invoice_form_totals")
     */
    public function getInvoiceFormTotals(Request $request)
    {
        $post = $request->request->get('recurring_invoice');
        if (!$post) {
            throw new NotFoundHttpException;
        }

        $response = $this->getInvoiceTotalsFromPost($post, new RecurringInvoice, $request->getLocale());

        return new JsonResponse($response);
    }

    protected function addTranslatedMessage($message, array $params = [], $status = 'success')
    {
        $translator = $this->get('translator');
        $this->get('session')
            ->getFlashBag()
            ->add($status, $translator->trans($message, $params, 'SiwappRecurringInvoiceBundle'));
    }
}
