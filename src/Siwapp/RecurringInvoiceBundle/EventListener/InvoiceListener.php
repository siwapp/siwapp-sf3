<?php

namespace Siwapp\RecurringInvoiceBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Siwapp\InvoiceBundle\Entity\Invoice;

class InvoiceListener
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();
        if (!$invoice instanceof Invoice) {
            return;
        }
        $entityManager = $args->getEntityManager();
        $repo = $entityManager->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice');
        foreach ($repo->findByInvoice($invoice) as $recurring) {
            $recurring->checkStatus();
        }
    }
}
