<?php

namespace Siwapp\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\CoreBundle\Entity\Item;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\InvoiceBundle\Entity\Payment;
use Siwapp\InvoiceBundle\Form\InvoiceType;

/**
 * @Route("/invoices")
 */
class InvoicesController extends Controller
{
    /**
     * @Route("", name="invoice_index")
     * @Template("SiwappInvoiceBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('SiwappInvoiceBundle:Invoice');
        $repo->setPaginator($this->get('knp_paginator'));
        // @todo Unhardcode this.
        $limit = 50;

        $form = $this->createForm('Siwapp\InvoiceBundle\Form\SearchInvoiceType', null, [
            'action' => $this->generateUrl('invoice_index'),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $pagination = $repo->paginatedSearch($form->getData(), $limit, $request->query->getInt('page', 1));
        } else {
            $pagination = $repo->paginatedSearch([], $limit, $request->query->getInt('page', 1));
        }

        $invoices = [];
        foreach ($pagination->getItems() as $item) {
            $invoices[] = $item[0];
        }
        $listForm = $this->createForm('Siwapp\InvoiceBundle\Form\InvoiceListType', $invoices, [
            'action' => $this->generateUrl('invoice_index'),
        ]);
        $listForm->handleRequest($request);
        if ($listForm->isValid()) {
            $data = $listForm->getData();
            if ($request->request->has('delete')) {
                foreach ($data['invoices'] as $invoice) {
                    $em->remove($invoice);
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Invoice(s) deleted.');

                // Rebuild the query, since some objects are now missing.
                return $this->redirect($this->generateUrl('invoice_index'));
            } elseif ($request->request->has('pdf') || $request->request->has('print')) {
                $pages = [];
                $settings = $em->getRepository('SiwappConfigBundle:Property')->getAll();
                foreach ($data['invoices'] as $invoice) {
                    $pages[] = $this->renderView('SiwappInvoiceBundle:Print:invoice.html.twig', array(
                        'invoice'  => $invoice,
                        'settings' => $settings,
                        'print' => $request->request->has('print'),
                    ));
                }

                $html = $this->get('siwapp_core.html_page_merger')->merge($pages, '<div class="pagebreak"> </div>');
                if ($request->request->has('pdf')) {
                    $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);

                    return new Response($pdf, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="Invoices.pdf"'
                    ]);
                } else {
                    return new Response($html);
                }
            } elseif ($request->request->has('email')) {
                foreach ($data['invoices'] as $invoice) {
                    $message = $this->getEmailMessage($invoice);
                    $result = $this->get('mailer')->send($message);
                    if ($result) {
                        $invoice->setSentByEmail(true);
                        $em->persist($invoice);
                    }
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Invoice(s) sent by email.');

                return $this->redirect($this->generateUrl('invoice_index'));
            }
        }

        return array(
            'invoices' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
        );
    }

    /**
     * @Route("/{id}/show", name="invoice_show")
     * @Template("SiwappInvoiceBundle:Default:show.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        if (!$entity->isClosed()) {
            // When the invoice is open send to the edit form by default.
            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/{id}/show/print", name="invoice_show_print")
     * @Template("SiwappInvoiceBundle:Print:invoice.html.twig")
     */
    public function showPrintAction($id)
    {
        $invoice = $this->getDoctrine()
            ->getRepository('SiwappInvoiceBundle:Invoice')
            ->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        return array(
            'invoice'  => $invoice,
            'settings' => $this->getDoctrine()->getRepository('SiwappConfigBundle:Property')->getAll(),
            'print' => true,
        );
    }

    /**
     * @Route("/{id}/show/pdf", name="invoice_show_pdf")
     */
    public function showPdfAction($id)
    {
        $invoice = $this->getDoctrine()
            ->getRepository('SiwappInvoiceBundle:Invoice')
            ->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        $html = $this->renderView('SiwappInvoiceBundle:Print:invoice.html.twig', array(
            'invoice'  => $invoice,
            'settings' => $this->getDoctrine()->getRepository('SiwappConfigBundle:Property')->getAll(),
        ));
        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Invoice-' . $invoice->label() . '.pdf"'
        ]);
    }

    /**
     * @Route("/new", name="invoice_add")
     * @Template("SiwappInvoiceBundle:Default:edit.html.twig")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new Invoice();
        $invoice->addItem(new Item());

        $form = $this->createForm('Siwapp\InvoiceBundle\Form\InvoiceType', $invoice, [
            'action' => $this->generateUrl('invoice_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($invoice);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Invoice added.');

            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $invoice->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/{id}/edit", name="invoice_edit")
     * @Template("SiwappInvoiceBundle:Default:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }
        $form = $this->createForm(InvoiceType::class, $entity, [
            'action' => $this->generateUrl('invoice_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($request->request->has('save_draft')) {
                $entity->setStatus(Invoice::DRAFT);
            } elseif ($request->request->has('save_close')) {
                $entity->setForcefullyClosed(true);
            } elseif ($entity->isDraft() && $request->request->has('save')) {
                $entity->setStatus(Invoice::OPENED);
            }
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Invoice updated.');

            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency'),
        );
    }

    /**
     * @Route("/{id}/email", name="invoice_email")
     * @Method({"POST"})
     */
    public function emailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        $message = $this->getEmailMessage($invoice);
        $result = $this->get('mailer')->send($message);
        if ($result) {
            $invoice->setSentByEmail(true);
            $em->persist($invoice);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Invoice sent by email.');
        }

        return $this->redirect($this->generateUrl('invoice_index'));
    }

    protected function getEmailMessage($invoice)
    {
        $em = $this->getDoctrine()->getManager();
        $configRepo = $em->getRepository('SiwappConfigBundle:Property');

        $html = $this->renderView('SiwappInvoiceBundle:Email:invoice.html.twig', array(
            'invoice'  => $invoice,
            'settings' => $em->getRepository('SiwappConfigBundle:Property')->getAll(),
        ));
        $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);
        $attachment = new \Swift_Attachment($pdf, $invoice->getId().'.pdf', 'application/pdf');
        $message = \Swift_Message::newInstance()
            ->setSubject($invoice->label())
            ->setFrom($configRepo->get('company_email'), $configRepo->get('company_name'))
            ->setTo($invoice->getCustomerEmail(), $invoice->getCustomerName())
            ->setBody($html, 'text/html')
            ->attach($attachment);

        return $message;
    }

    /**
     * @Route("/{id}/delete", name="invoice_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }
        $em->remove($invoice);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Invoice deleted.');

        return $this->redirect($this->generateUrl('invoice_index'));
    }

    /**
     * @Route("/{invoiceId}/payments", name="invoice_payments")
     * @Template("SiwappInvoiceBundle:Partials:payments.html.twig")
     */
    public function paymentsAction(Request $request, $invoiceId)
    {
        // Return all payments
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($invoiceId);

        $payment = new Payment;
        $addForm = $this->createForm('Siwapp\InvoiceBundle\Form\PaymentType', $payment, [
            'action' => $this->generateUrl('invoice_payments', ['invoiceId' => $invoiceId]),
        ]);
        $addForm->handleRequest($request);
        if ($addForm->isValid() && $invoice) {
            $invoice->addPayment($payment);
            $em->persist($invoice);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Payment added.');

            // Rebuild the query, since we have new objects now.
            return $this->redirect($this->generateUrl('invoice_index'));
        }

        $listForm = $this->createForm('Siwapp\InvoiceBundle\Form\InvoicePaymentListType', $invoice->getPayments()->getValues(), [
            'action' => $this->generateUrl('invoice_payments', ['invoiceId' => $invoiceId]),
        ]);
        $listForm->handleRequest($request);

        if ($listForm->isValid() && $invoice) {
            $data = $listForm->getData();
            foreach ($data['payments'] as $payment) {
                $invoice->removePayment($payment);
                $em->persist($invoice);
                $em->flush();
            }
            $this->get('session')->getFlashBag()->add('success', 'Payment(s) deleted.');

            // Rebuild the query, since some objects are now missing.
            return $this->redirect($this->generateUrl('invoice_index'));
        }

        return [
            'invoiceId' => $invoiceId,
            'add_form' => $addForm->createView(),
            'list_form' => $listForm->createView(),
        ];
    }
}
