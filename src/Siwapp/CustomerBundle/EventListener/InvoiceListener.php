<?php

namespace Siwapp\CustomerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Siwapp\InvoiceBundle\Entity\Invoice;

class InvoiceListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();
        if (!$invoice instanceof Invoice) {
            return;
        }

        $this->addRelationship($invoice, $args->getEntityManager());
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();
        if (!$invoice instanceof Invoice) {
            return;
        }

        $this->removeRelationship($invoice, $args->getEntityManager());
        $this->addRelationship($invoice, $args->getEntityManager());
    }

    protected function addRelationship(Invoice $invoice, $em)
    {
        $params = [
            'name' => $invoice->getCustomerName(),
            'identification' => $invoice->getCustomerIdentification(),
        ];
        $result = $em->getRepository('SiwappCustomerBundle:Customer')->findBy($params);
        if ($result) {
            $customer = reset($result);
            $customer->addInvoice($invoice);
            $em->persist($customer);
            $em->flush();
        }
    }

    protected function removeRelationship(Invoice $invoice, $em)
    {
        $customers = $em->getRepository('SiwappCustomerBundle:Customer')->findByInvoice($invoice);
        foreach ($customers as $customer) {
            $customer->removeInvoice($invoice);
            $em->persist($customer);
        }
        $em->flush();
    }
}
