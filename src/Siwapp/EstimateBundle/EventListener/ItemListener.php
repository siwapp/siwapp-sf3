<?php

namespace Siwapp\EstimateBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\EstimateBundle\Entity\Estimate;

class ItemListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(Estimate::class);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Item) {
                continue;
            }

            $result = $em->getRepository(Estimate::class)->findByItem($entity);
            foreach ($result as $estimate) {
                $estimate->checkAmounts();
                $uow->recomputeSingleEntityChangeSet($metadata, $estimate);
            }
        }
    }
}
