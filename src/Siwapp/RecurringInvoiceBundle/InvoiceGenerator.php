<?php

namespace Siwapp\RecurringInvoiceBundle;

use Doctrine\ORM\EntityManager;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

class InvoiceGenerator
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function generateAll()
    {
        $collection = $this->em->getRepository(RecurringInvoice::class)->findBy(['status' => RecurringInvoice::PENDING]);
        foreach ($collection as $recurring) {
            $this->generatePending($recurring);
        }
    }

    public function generatePending(RecurringInvoice $recurring)
    {
        $generated = 0;
        while ($recurring->countPendingInvoices($recurring) > 0) {
            $invoice = new Invoice;
            $invoice->setCustomerName($recurring->getCustomerName());
            $invoice->setCustomerEmail($recurring->getCustomerEmail());
            $invoice->setCustomerIdentification($recurring->getCustomerIdentification());
            $invoice->setContactPerson($recurring->getContactPerson());
            $invoice->setInvoicingAddress($recurring->getInvoicingAddress());
            $invoice->setShippingAddress($recurring->getShippingAddress());
            $invoice->setSerie($recurring->getSerie());
            foreach ($recurring->getItems() as $item) {
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
            $invoice->setNotes($recurring->getNotes());
            $invoice->setTerms($recurring->getTerms());
            if ($d = $recurring->getDaysToDue()) {
                $invoice->setDueDate(new \DateTime('+ ' . $d . ' days'));
            }

            $recurring->addInvoice($invoice);
            $generated++;
        }

        $this->em->persist($recurring);
        $this->em->flush();

        return $generated;
    }
}
