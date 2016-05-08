<?php

namespace Siwapp\DashboardBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\InvoiceBundle\Controller\AbstractInvoiceController;
use Siwapp\InvoiceBundle\Entity\Invoice;

class DashboardController extends AbstractInvoiceController
{
    /**
     * @Route("/", name="dashboard_index")
     * @Template("SiwappDashboardBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $invoiceRepo = $em->getRepository('SiwappInvoiceBundle:Invoice');
        $taxRepo = $em->getRepository('SiwappCoreBundle:Tax');

        $taxes = $taxRepo->findAll();
        $qb = $invoiceRepo->createQueryBuilder('i');

        $form = $this->createForm('Siwapp\InvoiceBundle\Form\SearchInvoiceType', null, [
            'action' => $this->generateUrl('dashboard_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isValid())
        {
            $this->applySearchFilters($qb, $form->getData());
            $entities = $qb->getQuery()->getResult();
            $overdue = array_filter($entities, function ($invoice) {
                return $invoice->isOverdue();
            });
        }
        else {
            $entities = $qb->getQuery()->getResult();
            $overdue = $invoiceRepo->findBy(['status' => Invoice::OVERDUE]);
        }

        $totals = ['gross' => 0, 'paid' => 0, 'due' => 0, 'overdue' => 0, 'net' => 0, 'tax' => 0, 'taxes' => []];
        foreach ($entities as $entity) {
            $totals['gross'] += $entity->getGrossAmount();
            $totals['paid'] += $entity->getPaidAmount();
            $totals['due'] += $entity->getDueAmount();
            $totals['net'] += $entity->getNetAmount();
            $totals['tax'] += $entity->getTaxAmount();
            foreach ($taxes as $tax) {
                if (!isset($totals['taxes'][$tax->getId()])) {
                    $totals['taxes'][$tax->getId()] = 0;
                }
                $totals['taxes'][$tax->getId()] += $entity->__get('tax_amount_' . $tax->getName());
            }
        }
        foreach ($overdue as $entity) {
            $totals['overdue'] += $entity->getDueAmount();
        }

        return array(
            'entities' => array_slice($entities, 0, 5),
            'overdue_invoices' => $overdue,
            'currency' => 'EUR',
            'search_form' => $form->createView(),
            'totals' => $totals,
            'taxes' => $taxes,
        );
    }
}
