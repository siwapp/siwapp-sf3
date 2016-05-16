<?php

namespace Siwapp\CustomerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Siwapp\CoreBundle\Entity\AbstractInvoice;

class InvoiceListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();
        if (!$invoice instanceof AbstractInvoice) {
            return;
        }

        switch (get_class($invoice)) {
            case 'Siwapp\InvoiceBundle\Entity\Invoice':
                $this->addInvoiceRelationship($invoice, $args->getEntityManager());
                break;
            case 'Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice':
                $this->addRecurringInvoiceRelationship($invoice, $args->getEntityManager());
                break;
            case 'Siwapp\EstimateBundle\Entity\Estimate':
                $this->addEstimateRelationship($invoice, $args->getEntityManager());
                break;
        }

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();
        if (!$invoice instanceof AbstractInvoice) {
            return;
        }

        switch (get_class($invoice)) {
            case 'Siwapp\InvoiceBundle\Entity\Invoice':
                $this->removeInvoiceRelationship($invoice, $args->getEntityManager());
                $this->addInvoiceRelationship($invoice, $args->getEntityManager());
                break;
            case 'Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice':
                $this->removeRecurringInvoiceRelationship($invoice, $args->getEntityManager());
                $this->addRecurringInvoiceRelationship($invoice, $args->getEntityManager());
                break;
            case 'Siwapp\EstimateBundle\Entity\Estimate':
                $this->removeEstimateRelationship($invoice, $args->getEntityManager());
                $this->addEstimateRelationship($invoice, $args->getEntityManager());
                break;
        }
    }

    protected function findCustomer(AbstractInvoice $invoice, $em)
    {
        $params = [
            'name' => $invoice->getCustomerName(),
            'identification' => $invoice->getCustomerIdentification(),
        ];
        $result = $em->getRepository('SiwappCustomerBundle:Customer')->findBy($params);

        return reset($result);
    }

    protected function addInvoiceRelationship(AbstractInvoice $invoice, $em)
    {
        $customer = $this->findCustomer($invoice, $em);
        if ($customer) {
            $customer->addInvoice($invoice);
            $em->persist($customer);
            $em->flush();
        }
    }

    protected function addRecurringInvoiceRelationship(AbstractInvoice $invoice, $em)
    {
        $customer = $this->findCustomer($invoice, $em);
        if ($customer) {
            $customer->addRecurringInvoice($invoice);
            $em->persist($customer);
            $em->flush();
        }
    }

    protected function addEstimateRelationship(AbstractInvoice $invoice, $em)
    {
        $customer = $this->findCustomer($invoice, $em);
        if ($customer) {
            $customer->removeEstimate($invoice);
            $em->persist($customer);
            $em->flush();
        }
    }

    protected function removeInvoiceRelationship(AbstractInvoice $invoice, $em)
    {
        $customers = $em->getRepository('SiwappCustomerBundle:Customer')->findByInvoice($invoice);
        foreach ($customers as $customer) {
            $customer->removeInvoice($invoice);
            $em->persist($customer);
        }
        $em->flush();
    }

    protected function removeRecurringInvoiceRelationship(AbstractInvoice $invoice, $em)
    {
        $customers = $em->getRepository('SiwappCustomerBundle:Customer')->findByRecurringInvoice($invoice);
        foreach ($customers as $customer) {
            $customer->removeRecurringInvoice($invoice);
            $em->persist($customer);
        }
        $em->flush();
    }

    protected function removeEstimateRelationship(AbstractInvoice $invoice, $em)
    {
        $customers = $em->getRepository('SiwappCustomerBundle:Customer')->findByEstimate($invoice);
        foreach ($customers as $customer) {
            $customer->removeEstimate($invoice);
            $em->persist($customer);
        }
        $em->flush();
    }
}
