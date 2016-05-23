<?php

namespace Siwapp\RecurringInvoiceBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

class ItemListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(RecurringInvoice::class);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Item) {
                continue;
            }

            $result = $em->getRepository(RecurringInvoice::class)->findByItem($entity);
            foreach ($result as $recurring) {
                $recurring->checkAmounts();
                $uow->recomputeSingleEntityChangeSet($metadata, $recurring);
            }
        }
    }
}
