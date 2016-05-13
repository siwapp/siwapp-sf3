<?php

namespace Siwapp\EstimateBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\CoreBundle\Entity\Item;
use Siwapp\EstimateBundle\Entity\Estimate;
use Siwapp\EstimateBundle\Form\EstimateType;

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
     * @Template("SiwappEstimateBundle:Default:show.html.twig")
     */
    public function showAction($id)
    {
        $entity = $this->getDoctrine()
            ->getRepository('SiwappEstimateBundle:Estimate')
            ->find($id);

        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/add", name="estimate_add")
     * @Template("SiwappEstimateBundle:Default:edit.html.twig")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $estimate = new Estimate();
        $estimate->addItem(new Item());

        $form = $this->createForm(EstimateType::class, $estimate, [
            'action' => $this->generateUrl('estimate_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($estimate);
            $em->flush();

            return $this->redirect($this->generateUrl('estimate_edit', array('id' => $estimate->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $estimate,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/{id}/edit", name="estimate_edit")
     * @Template("SiwappEstimateBundle:Default:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SiwappEstimateBundle:Estimate')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estimate entity.');
        }
        $form = $this->createForm(EstimateType::class, $entity, [
            'action' => $this->generateUrl('estimate_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($request->request->has('save_draft')) {
                $entity->setStatus(Estimate::DRAFT);
            }
            elseif ($request->request->has('save_close')) {
                $entity->setStatus(Estimate::REJECTED);
            }
            elseif ($entity->isDraft() && $request->request->has('save')) {
                $entity->setStatus(Estimate::APPROVED);
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('estimate_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
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
