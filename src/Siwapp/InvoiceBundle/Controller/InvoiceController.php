<?php

namespace Siwapp\InvoiceBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Siwapp\CoreBundle\Controller\AbstractInvoiceController;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\InvoiceBundle\Entity\Payment;
use Siwapp\InvoiceBundle\Form\InvoiceType;

/**
 * @Route("/invoice")
 */
class InvoiceController extends AbstractInvoiceController
{
    /**
     * @Route("", name="invoice_index")
     * @Template("SiwappInvoiceBundle:Invoice:index.html.twig")
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
        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($listForm->isSubmitted() && $listForm->isValid()) {
            $data = $listForm->getData();
            if (empty($data['invoices'])) {
                $this->addTranslatedMessage('flash.nothing_selected', 'warning');
            }
            else {
                if ($request->request->has('delete')) {
                    return $this->bulkDelete($data['invoices']);
                } elseif ($request->request->has('pdf')) {
                    return $this->bulkPdf($data['invoices']);
                } elseif ($request->request->has('print')) {
                    return $this->bulkPrint($data['invoices']);
                } elseif ($request->request->has('email')) {
                    return $this->bulkEmail($data['invoices']);
                }
            }
        }

        return array(
            'invoices' => $pagination,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
            'search_form' => $form->createView(),
            'list_form' => $listForm->createView(),
        );
    }

    /**
     * @Route("/{id}/show", name="invoice_show")
     * @Template("SiwappInvoiceBundle:Invoice:show.html.twig")
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
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
        );
    }

    /**
     * @Route("/{id}/show/print", name="invoice_show_print")
     */
    public function showPrintAction($id)
    {
        $invoice = $this->getDoctrine()
            ->getRepository('SiwappInvoiceBundle:Invoice')
            ->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        return new Response($this->getInvoicePrintPdfHtml($invoice, true));
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

        $html = $this->getInvoicePrintPdfHtml($invoice);
        $pdf = $this->getPdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Invoice-' . $invoice->label() . '.pdf"'
        ]);
    }

    /**
     * @Route("/new", name="invoice_add")
     * @Template("SiwappInvoiceBundle:Invoice:edit.html.twig")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new Invoice();
        $newItem = new Item($em->getRepository('SiwappCoreBundle:Tax')->findBy(['is_default' => 1]));
        $invoice->addItem($newItem);
        $terms = $em->getRepository('SiwappConfigBundle:Property')->get('legal_terms');
        if ($terms) {
            $invoice->setTerms($terms);
        }

        $form = $this->createForm('Siwapp\InvoiceBundle\Form\InvoiceType', $invoice, [
            'action' => $this->generateUrl('invoice_add'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('save_draft')) {
                $invoice->setStatus(Invoice::DRAFT);
            } else {
                // Any save action transforms this to opened.
                $invoice->setStatus(Invoice::OPENED);
            }
            $em->persist($invoice);
            $em->flush();
            $this->addTranslatedMessage('flash.added');

            // Send the email after the invoice is updated.
            if ($request->request->has('save_email')) {
                $message = $this->getEmailMessage($invoice);
                $result = $this->get('mailer')->send($message);
                if ($result) {
                    $this->addTranslatedMessage('flash.emailed');
                    if (!$invoice->isSentByEmail()) {
                        $invoice->setSentByEmail(true);
                        $em->persist($invoice);
                        $em->flush();
                    }
                }
            }

            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $invoice->getId())));
        }

        return array(
            'form' => $form->createView(),
            'entity' => $invoice,
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
        );
    }

    /**
     * @Route("/{id}/edit", name="invoice_edit")
     * @Template("SiwappInvoiceBundle:Invoice:edit.html.twig")
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

        if ($form->isSubmitted() && $form->isValid()) {
            $redirectRoute = 'invoice_edit';
            if ($request->request->has('save_draft')) {
                $entity->setStatus(Invoice::DRAFT);
            } elseif ($request->request->has('save_close')) {
                $entity->setForcefullyClosed(true);
            } elseif ($entity->isDraft()) {
                // Any save action transforms this to opened.
                $entity->setStatus(Invoice::OPENED);
            }

            // See if one of PDF/Print buttons was clicked.
            if ($request->request->has('save_pdf')) {
                $redirectRoute = 'invoice_show_pdf';
            } elseif ($request->request->has('save_print')) {
                $this->get('session')->set('invoice_auto_print', $id);
            }
            // Save.
            $em->persist($entity);
            $em->flush();
            $this->addTranslatedMessage('flash.updated');

            // Send the email after the invoice is updated.
            if ($request->request->has('save_email')) {
                $message = $this->getEmailMessage($entity);
                $result = $this->get('mailer')->send($message);
                if ($result) {
                    $this->addTranslatedMessage('flash.emailed');
                    if (!$entity->isSentByEmail()) {
                        $entity->setSentByEmail(true);
                        $em->persist($entity);
                        $em->flush();
                    }
                }
            }

            return $this->redirect($this->generateUrl($redirectRoute, array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
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
            $this->addTranslatedMessage('flash.emailed');
        }

        return $this->redirect($this->generateUrl('invoice_index'));
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
        $this->addTranslatedMessage('flash.deleted');

        return $this->redirect($this->generateUrl('invoice_index'));
    }

    /**
     * @Route("/{invoiceId}/payments", name="invoice_payments")
     * @Template("SiwappInvoiceBundle:Payment:list.html.twig")
     */
    public function paymentsAction(Request $request, $invoiceId)
    {
        // Return all payments
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('SiwappInvoiceBundle:Invoice')->find($invoiceId);
        if (!$invoice) {
            throw $this->createNotFoundException('Unable to find Invoice entity.');
        }

        $payment = new Payment;
        $addForm = $this->createForm('Siwapp\InvoiceBundle\Form\PaymentType', $payment, [
            'action' => $this->generateUrl('invoice_payments', ['invoiceId' => $invoiceId]),
        ]);
        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $invoice->addPayment($payment);
            $em->persist($invoice);
            $em->flush();
            $this->addTranslatedMessage('payment.flash.added');

            // Rebuild the query, since we have new objects now.
            return $this->redirect($this->generateUrl('invoice_index'));
        }

        $listForm = $this->createForm('Siwapp\InvoiceBundle\Form\InvoicePaymentListType', $invoice->getPayments()->getValues(), [
            'action' => $this->generateUrl('invoice_payments', ['invoiceId' => $invoiceId]),
        ]);
        $listForm->handleRequest($request);

        if ($listForm->isSubmitted() && $listForm->isValid()) {
            $data = $listForm->getData();
            foreach ($data['payments'] as $payment) {
                $invoice->removePayment($payment);
                $em->persist($invoice);
                $em->flush();
            }
            $this->addTranslatedMessage('payment.flash.bulk_deleted');

            // Rebuild the query, since some objects are now missing.
            return $this->redirect($this->generateUrl('invoice_index'));
        }

        return [
            'invoiceId' => $invoiceId,
            'add_form' => $addForm->createView(),
            'list_form' => $listForm->createView(),
            'currency' => $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR'),
        ];
    }

    /**
     * @Route("/form-totals", name="invoice_form_totals")
     */
    public function getInvoiceFormTotals(Request $request)
    {
        $post = $request->request->get('invoice');
        if (!$post) {
            throw new NotFoundHttpException;
        }

        $response = $this->getInvoiceTotalsFromPost($post, new Invoice, $request->getLocale());

        return new JsonResponse($response);
    }

    protected function addTranslatedMessage($message, $status = 'success')
    {
        $translator = $this->get('translator');
        $this->get('session')
            ->getFlashBag()
            ->add($status, $translator->trans($message, [], 'SiwappInvoiceBundle'));
    }

    protected function getInvoicePrintPdfHtml(Invoice $invoice, $print = false)
    {
        $settings = $this->getDoctrine()
            ->getRepository('SiwappConfigBundle:Property')
            ->getAll();

        return $this->renderView('SiwappInvoiceBundle:Invoice:print.html.twig', [
            'invoice'  => $invoice,
            'settings' => $settings,
            'print' => $print,
        ]);
    }

    protected function bulkDelete(array $invoices)
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($invoices as $invoice) {
            $em->remove($invoice);
        }
        $em->flush();
        $this->addTranslatedMessage('flash.bulk_deleted');

        return $this->redirect($this->generateUrl('invoice_index'));
    }

    protected function bulkPdf(array $invoices)
    {
        $pages = [];
        foreach ($invoices as $invoice) {
            $pages[] = $this->getInvoicePrintPdfHtml($invoice);
        }

        $html = $this->get('siwapp_core.html_page_merger')->merge($pages, '<div class="pagebreak"> </div>');
        $pdf = $this->getPdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Invoices.pdf"'
        ]);
    }

    protected function bulkPrint(array $invoices)
    {
        $pages = [];
        foreach ($invoices as $invoice) {
            $pages[] = $this->getInvoicePrintPdfHtml($invoice, true);
        }

        $html = $this->get('siwapp_core.html_page_merger')->merge($pages, '<div class="pagebreak"> </div>');

        return new Response($html);
    }

    protected function bulkEmail(array $invoices)
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($invoices as $invoice) {
            $message = $this->getEmailMessage($invoice);
            $result = $this->get('mailer')->send($message);
            if ($result) {
                $invoice->setSentByEmail(true);
                $em->persist($invoice);
            }
        }
        $em->flush();
        $this->addTranslatedMessage('flash.bulk_emailed');

        return $this->redirect($this->generateUrl('invoice_index'));
    }

    protected function getEmailMessage($invoice)
    {
        $em = $this->getDoctrine()->getManager();
        $configRepo = $em->getRepository('SiwappConfigBundle:Property');

        $html = $this->renderView('SiwappInvoiceBundle:Invoice:email.html.twig', array(
            'invoice'  => $invoice,
            'settings' => $em->getRepository('SiwappConfigBundle:Property')->getAll(),
        ));
        $pdf = $this->getPdf($html);
        $attachment = new \Swift_Attachment($pdf, $invoice->getId().'.pdf', 'application/pdf');
        $subject = '[' . $this->get('translator')->trans('invoice.invoice', [], 'SiwappInvoiceBundle') . ': ' . $invoice->label() . ']';
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($configRepo->get('company_email'), $configRepo->get('company_name'))
            ->setTo($invoice->getCustomerEmail(), $invoice->getCustomerName())
            ->setBody($html, 'text/html')
            ->attach($attachment);

        return $message;
    }
}
