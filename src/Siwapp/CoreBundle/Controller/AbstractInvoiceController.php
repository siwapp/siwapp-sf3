<?php

namespace Siwapp\CoreBundle\Controller;

use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Siwapp\CoreBundle\Entity\Item;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractInvoiceController extends Controller
{

    protected function getPdf(string $html): string
    {
        $config = $this->getDoctrine()->getRepository('SiwappConfigBundle:Property');
        $pdfSize = $config->get('pdf_size');
        $pdfOrientation = $config->get('pdf_orientation');
        $config = [];
        if ($pdfSize) {
            $config['page-size'] = $pdfSize;
        }
        if ($pdfOrientation) {
            $config['orientation'] = $pdfOrientation;
        }

        return $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $config);
    }

    protected function getInvoiceTotalsFromPost(array $post, AbstractInvoice $invoice, string $locale): array
    {
        $em = $this->getDoctrine()->getManager();
        $taxRepo = $em->getRepository('SiwappCoreBundle:Tax');
        $currency = $em->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR');
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $transformer = new MoneyToLocalizedStringTransformer(2, true);

        $totals = [];
        foreach ($post['items'] as $index => $postItem) {
            $item = new Item;
            $item->setUnitaryCost($transformer->reverseTransform($postItem['unitary_cost']));
            $item->setQuantity($postItem['quantity']);
            $item->setDiscount($postItem['discount_percent']);
            if (isset($postItem['taxes'])) {
                foreach($postItem['taxes'] as $taxId) {
                    $tax = $taxRepo->find($taxId);
                    if (!$tax) {
                        continue;
                    }
                    $item->addTax($tax);
                }
            }
            $totals['items'][$index] = [
                'gross_amount' => $formatter->formatCurrency($item->getGrossAmount(), $currency),
            ];
            $invoice->addItem($item);
        }
        $invoice->checkAmounts();

        $totals += [
            'invoice_base_amount' => $formatter->formatCurrency($invoice->getBaseAmount(), $currency),
            'invoice_tax_amount' => $formatter->formatCurrency($invoice->getTaxAmount(), $currency),
            'invoice_gross_amount' => $formatter->formatCurrency($invoice->getGrossAmount(), $currency),
        ];

        return $totals;
    }
}
