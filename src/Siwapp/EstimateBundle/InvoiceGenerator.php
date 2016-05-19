<?php

namespace Siwapp\EstimateBundle;

use Doctrine\ORM\EntityManager;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\EstimateBundle\Entity\Estimate;
use Siwapp\InvoiceBundle\Entity\Invoice;

class InvoiceGenerator
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function generate(Estimate $estimate)
    {
        $invoice = new Invoice;
        $invoice->setCustomerName($estimate->getCustomerName());
        $invoice->setCustomerEmail($estimate->getCustomerEmail());
        $invoice->setCustomerIdentification($estimate->getCustomerIdentification());
        $invoice->setContactPerson($estimate->getContactPerson());
        $invoice->setInvoicingAddress($estimate->getInvoicingAddress());
        $invoice->setShippingAddress($estimate->getShippingAddress());
        $invoice->setSeries($estimate->getSeries());
        foreach ($estimate->getItems() as $item) {
            $invoiceItem = new Item;
            $invoiceItem->setDescription($item->getDescription());
            $invoiceItem->setQuantity($item->getQuantity());
            $invoiceItem->setDiscount($item->getDiscount());
            $invoiceItem->setUnitaryCost($item->getUnitaryCost());
            foreach ($item->getTaxes() as $tax) {
                $invoiceItem->addTax($tax);
            }
            $invoice->addItem($invoiceItem);
        }
        $invoice->setNotes($estimate->getNotes());
        $invoice->setTerms($estimate->getTerms());

        $this->em->persist($invoice);
        $this->em->flush();

        return $invoice;
    }
}
