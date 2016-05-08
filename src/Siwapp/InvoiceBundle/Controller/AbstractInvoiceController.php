<?php

namespace Siwapp\InvoiceBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractInvoiceController extends Controller
{
    public function applySearchFilters(QueryBuilder $qb, array $data)
    {
        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }
            if ($field == 'terms') {
                $qb->join('i.serie', 's', 'WITH', 'i.serie = s.id');
                $terms = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('i.number', $terms),
                    $qb->expr()->like('s.name', $terms),
                    $qb->expr()->like("CONCAT(s.name, ' ', i.number)", $terms)
                ));
            }
            elseif ($field == 'date_from') {
                $qb->andWhere('i.issue_date >= :date_from');
                $qb->setParameter('date_from', $value);
            }
            elseif ($field == 'date_to') {
                $qb->andWhere('i.issue_date <= :date_to');
                $qb->setParameter('date_to', $value);
            }
            elseif ($field == 'status') {
                $qb->andWhere('i.status = :status');
                $qb->setParameter('status', $value);
            }
            elseif ($field == 'customer') {
                $customer = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('i.customer_name', $customer),
                    $qb->expr()->like('i.customer_identification', $customer)
                ));
            }
            elseif ($field == 'serie') {
                $qb->andWhere('i.serie = :series');
                $qb->setParameter('series', $value);
            }
        }
    }
}
