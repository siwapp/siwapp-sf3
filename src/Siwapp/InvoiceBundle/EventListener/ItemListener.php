<?php

namespace Siwapp\InvoiceBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\InvoiceBundle\Entity\Invoice;

class ItemListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(Invoice::class);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Item) {
                continue;
            }

            $result = $em->getRepository(Invoice::class)->findByItem($entity);
            foreach ($result as $invoice) {
                $invoice->checkAmounts();
                $uow->recomputeSingleEntityChangeSet($metadata, $invoice);
            }
        }
    }
}
