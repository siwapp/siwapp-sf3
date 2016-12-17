<?php

namespace Siwapp\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siwapp\ProductBundle\Entity\Product;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("", name="product_index")
     * @Template("SiwappProductBundle:Product:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('SiwappProductBundle:Product');
        $repo->setPaginator($this->get('knp_paginator'));
        // @todo Unhardcode this.
        $limit = 50;

        $form = $this->createForm('Siwapp\ProductBundle\Form\SearchProductType', null, [
            'action' => $this->generateUrl('product_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pagination = $repo->paginatedSearch($form->getData(), $limit, $request->query->getInt('page', 1));
        } else {
            $pagination = $repo->paginatedSearch([], $limit, $request->query->getInt('page', 1));
        }

        $products = [];
        foreach ($pagination->getItems() as $item) {
            $products[] = $item[0];
        }
        $listForm = $this->createForm('Siwapp\ProductBundle\Form\ProductListType', $products, [
            'action' => $this->generateUrl('product_index'),
        ]);
        $listForm->handleRequest($request);
        if ($listForm->isSubmitted() && $listForm->isValid()) {
            $data = $listForm->getData();
            if ($request->request->has('delete')) {
                if (empty($data['products'])) {
                    $this->addTranslatedMessage('flash.nothing_selected', 'warning');
                }
                else {
                    foreach ($data['products'] as $product) {
                        $em->remove($product);
                    }
                    $em->flush();
                    $this->addTranslatedMessage('flash.bulk_deleted');

                    // Rebuild the query, since some objects are now missing.
                    return $this->redirect($this->generateUrl('product_index'));
                }
            }
        }

        return array(
            'products' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
        );
    }

    /**
     * @Route("/autocomplete-reference", name="product_autocomplete_reference")
     */
    public function autocompleteReferenceAction(Request $request)
    {
        $entities = $this->getDoctrine()
            ->getRepository('SiwappProductBundle:Product')
            ->findLikeReference($request->get('term'));

        return new JsonResponse($entities);
    }

    /**
     * @Route("/autocomplete-description", name="product_autocomplete_description")
     */
    public function autocompleteDescriptionAction(Request $request)
    {
        $entities = $this->getDoctrine()
            ->getRepository('SiwappProductBundle:Product')
            ->findLikeDescription($request->get('term'));

        return new JsonResponse($entities);
    }

    /**
     * @Route("/add", name="product_add")
     * @Template("SiwappProductBundle:Product:edit.html.twig")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = new Product();

        $form = $this->createForm('Siwapp\ProductBundle\Form\ProductType', $product, [
            'action' => $this->generateUrl('product_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addTranslatedMessage('flash.added');

            return $this->redirect($this->generateUrl('product_edit', array('id' => $product->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $product,
        );
    }

    /**
     * @Route("/{id}/edit", name="product_edit")
     * @Template("SiwappProductBundle:Product:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('SiwappProductBundle:Product')->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $form = $this->createForm('Siwapp\ProductBundle\Form\ProductType', $product, [
            'action' => $this->generateUrl('product_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addTranslatedMessage('flash.updated');

            return $this->redirect($this->generateUrl('product_edit', array('id' => $product->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $product,
        );
    }

    /**
     * @Route("/{id}/delete", name="product_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('SiwappProductBundle:Product')->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }
        $em->remove($product);
        $em->flush();
        $this->addTranslatedMessage('flash.deleted');

        return $this->redirect($this->generateUrl('product_index'));
    }

    protected function addTranslatedMessage($message, $status = 'success')
    {
        $translator = $this->get('translator');
        $this->get('session')
            ->getFlashBag()
            ->add($status, $translator->trans($message, [], 'SiwappProductBundle'));
    }
}
