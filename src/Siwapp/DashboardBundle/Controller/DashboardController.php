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
        $em = $this->getDoctrine()->getManager();
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
        }

        $invoicesQb = clone $qb;
        $invoicesQb->setMaxResults(5);
        $invoices = $invoicesQb->getQuery()->getResult();

        $overdueQb = clone $qb;
        $overdueQb->andWhere('i.status = :overdue')
            ->setParameter('overdue', Invoice::OVERDUE);
        $overdue = $overdueQb->getQuery()->getResult();

        // Totals.
        $qb->addSelect('SUM(i.gross_amount)');
        $qb->addSelect('SUM(i.paid_amount)');
        $qb->addSelect('SUM(i.gross_amount - i.paid_amount)');
        $qb->addSelect('SUM(i.net_amount)');
        $qb->addSelect('SUM(i.tax_amount)');
        $qb->setMaxResults(1);
        $result = $qb->getQuery()->getSingleResult();
        // Transform to named array for easier access in template.
        $totals = [
            'gross' => $result[1],
            'paid' => $result[2],
            'due' => $result[3],
            'net' => $result[4],
            'tax' => $result[5],
        ];
        // Overdue total.
        $overdueQb->addSelect('SUM(i.gross_amount - i.paid_amount)');
        $result = $overdueQb->getQuery()->getSingleResult();
        $totals['overdue'] = $result[1];

        // Tax totals.
        foreach ($taxes as $tax) {
            $taxQb = clone $qb;
            $taxQb->join('i.items', 'it');
            $taxQb->join('it.taxes', 'tx');
            $taxQb->addSelect('SUM(it.unitary_cost * (tx.value/100))');
            $taxQb->andWhere('tx.id = :tax')
                ->setParameter('tax', $tax);
            $totals['taxes'][$tax->getId()] = $taxQb->getQuery()->getSingleResult()[6];
        }

        return array(
            'invoices' => $invoices,
            'overdue_invoices' => $overdue,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'totals' => $totals,
            'taxes' => $taxes,
            'paginatable' => false,
            'sortable' => false,
        );
    }
}
