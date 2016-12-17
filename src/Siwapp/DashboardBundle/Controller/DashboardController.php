<?php

namespace Siwapp\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\InvoiceBundle\Controller\AbstractInvoiceController;
use Siwapp\InvoiceBundle\Entity\Invoice;

class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboard_index")
     * @Template("SiwappDashboardBundle:Dashboard:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoiceRepo = $em->getRepository('SiwappInvoiceBundle:Invoice');
        $invoiceRepo->setPaginator($this->get('knp_paginator'));

        $taxRepo = $em->getRepository('SiwappCoreBundle:Tax');
        $taxes = $taxRepo->findAll();

        $form = $this->createForm('Siwapp\InvoiceBundle\Form\SearchInvoiceType', null, [
            'action' => $this->generateUrl('dashboard_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $params = $form->getData();
        } else {
            $params = [];
        }
        // Last invoices.
        // @todo Unhardcode this.
        $limit = 5;
        $pagination = $invoiceRepo->paginatedSearch($params, $limit, $request->query->getInt('page', 1));
        $totals = $invoiceRepo->getTotals($params);

        // Last overdue invoices.
        $overdueParams = $params;
        $overdueParams['status'] = Invoice::OVERDUE;
        // @todo Unhardcode this.
        $limit = 50;
        $paginationDue = $invoiceRepo->paginatedSearch($overdueParams, $limit, $request->query->getInt('page', 1));
        $totalsDue = $invoiceRepo->getTotals($overdueParams);
        $totals['overdue'] = $totalsDue['due'];

        // Tax totals.
        foreach ($taxes as $tax) {
            $taxId = $tax->getId();
            $params['tax'] = $taxId;
            $taxTotals = $invoiceRepo->getTotals($params);
            $totals['taxes'][$taxId] = $taxTotals['tax_' . $taxId];
        }

        return [
            'invoices' => $pagination,
            'overdue_invoices' => $paginationDue,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'totals' => $totals,
            'taxes' => $taxes,
            'paginatable' => false,
            'sortable' => false,
        ];
    }
}
