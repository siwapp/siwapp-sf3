<?php

namespace Siwapp\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/customers")
 */
class CustomersController extends Controller
{
    /**
     * @Route("", name="customer_index")
     * @Template("SiwappCustomerBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('SiwappCustomerBundle:Customer')->createQueryBuilder('c');

        $form = $this->createForm('Siwapp\CustomerBundle\Form\SearchCustomerType', null, [
            'action' => $this->generateUrl('customer_index'),
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


        $listForm = $this->createForm('Siwapp\CustomerBundle\Form\CustomerListType', $pagination->getItems(), [
            'action' => $this->generateUrl('customer_index'),
        ]);
        $listForm->handleRequest($request);
        if ($listForm->isValid()) {
            $data = $listForm->getData();
            if ($request->request->has('delete')) {
                foreach ($data['customers'] as $customer) {
                    $em->remove($customer);
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Customer(s) deleted.');

                // Rebuild the query, since some objects are now missing.
                return $this->redirect($this->generateUrl('customer_index'));
            }
        }

        return array(
            'customers' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
        );
    }

    /**
     * @Route("/add", name="customer_add")
     * @Template("SiwappCustomerBundle:Default:edit.html.twig")
     */
    public function addAction(Request $request)
    {
    }

    /**
     * @Route("/{id}/delete", name="customer_delete")
     */
    public function deleteAction(Request $request)
    {
    }
}
