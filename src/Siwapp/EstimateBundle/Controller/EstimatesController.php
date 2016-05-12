<?php

namespace Siwapp\EstimateBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/estimates")
 */
class EstimatesController extends Controller
{
    /**
     * @Route("/", name="estimate_index")
     * @Template("SiwappEstimateBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('SiwappEstimateBundle:Estimate')->createQueryBuilder('e');

        $form = $this->createForm('Siwapp\EstimateBundle\Form\SearchEstimateType', null, [
            'action' => $this->generateUrl('estimate_index'),
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

        return array(
            'invoices' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/show", name="estimate_show")
     * @Template
     */
    public function showAction($id)
    {
        return array();
    }

    /**
     * @Route("/add", name="estimate_add")
     * @Template("SiwappEstimateBundle:Default:edit.html.twig")
     */
    public function addAction()
    {
        return array();
    }

    /**
     * @Route("/create", name="estimate_create")
     * @Method("POST")
     * @Template("SiwappEstimateBundle:Default:edit.html.twig")
     */
    public function createAction()
    {
        return $this->redirect($this->generateUrl('estimate_edit'));
    }

    /**
     * @Route("/edit", name="estimate_edit")
     * @Template
     */
    public function editAction()
    {
        return array();
    }

    /**
     * @Route("/update", name="estimate_update")
     * @Method("POST")
     * @Template("SiwappEstimateBundle:Default:edit.html.twig")
     */
    public function updateAction()
    {
        return $this->redirect($this->generateUrl('estimate_edit'));
    }

    /**
     * @Route("/delete", name="estimate_delete")
     */
    public function deleteAction()
    {
        return $this->redirect($this->generateUrl('estimate_index'));
    }

    protected function applySearchFilters(QueryBuilder $qb, array $data)
    {
        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }
            if ($field == 'terms') {
                $qb->join('e.serie', 's', 'WITH', 'e.serie = s.id');
                $terms = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('s.name', $terms)
                ));
            }
            elseif ($field == 'date_from') {
                $qb->andWhere('e.issue_date >= :date_from');
                $qb->setParameter('date_from', $value);
            }
            elseif ($field == 'date_to') {
                $qb->andWhere('e.issue_date <= :date_to');
                $qb->setParameter('date_to', $value);
            }
            elseif ($field == 'status') {
                $qb->andWhere('e.status = :status');
                $qb->setParameter('status', $value);
            }
            elseif ($field == 'customer') {
                $customer = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('e.customer_name', $customer),
                    $qb->expr()->like('e.customer_identification', $customer)
                ));
            }
            elseif ($field == 'serie') {
                $qb->andWhere('e.serie = :series');
                $qb->setParameter('series', $value);
            }
        }
    }
}
