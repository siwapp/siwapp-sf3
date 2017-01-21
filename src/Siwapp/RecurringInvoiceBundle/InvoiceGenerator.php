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
        $count = 0;
        $collection = $this->em->getRepository(RecurringInvoice::class)->findBy([
            'status' => [RecurringInvoice::PENDING, RecurringInvoice::ACTIVE],
        ]);
        foreach ($collection as $recurring) {
            $count += $this->generatePending($recurring);
        }

        return $count;
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
            $invoice->setSeries($recurring->getSeries());
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
            // Set invoice as Opened.
            $invoice->setStatus(Invoice::OPENED);

            $recurring->addInvoice($invoice);
            $generated++;
        }

        $recurring->setLastExecutionDate(new \DateTime);
        $this->em->persist($recurring);
        $this->em->flush();

        return $generated;
    }
}
