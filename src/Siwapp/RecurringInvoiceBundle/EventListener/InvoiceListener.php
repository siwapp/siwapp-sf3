<?php

namespace Siwapp\RecurringInvoiceBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

class InvoiceListener
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $invoice = $args->getEntity();

        $entityManager = $args->getEntityManager();
        $repo = $entityManager->getRepository('SiwappRecurringInvoiceBundle:RecurringInvoice');
        foreach ($repo->findByInvoice($invoice) as $recurring) {
            $recurring->checkStatus();
        }
    }
}
