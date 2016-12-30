<?php

namespace Siwapp\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\CoreBundle\Entity\Series;

/**
 * Repository class to be inherited by InvoiceRepository,
 * RecurringInvoiceRepository and EstimateRepository
 */
class AbstractInvoiceRepository extends EntityRepository
{
    /**
     * getNextNumber
     * Obtain the next numer available for the provided series
     * @param \Siwapp\CoreBundle\Entity\Series $serie
     * @return integer
     */
    public function getNextNumber(Series $series)
    {
        $class = $this->getEntityName();
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('MAX(i.number) AS max_number')
            ->from($class, 'i')
            ->where('i.status <> :status')
            ->andWhere('i.series = :series')
            ->setParameter('status', $class::DRAFT)
            ->setParameter('series', $series)
            ->getQuery()
            ->getSingleResult();

        return !empty($result['max_number'])
            ? $result['max_number'] + 1
            : $series->getFirstNumber();
    }

    /**
     * Update totals for invoices, recurring or estimates
     * @param ArrayCollection of entities
     * @return AbstractInvoiceRepository
     **/
    public function updateTotals()
    {
        $em = $this->getEntityManager();
        foreach ($em->createQuery('SELECT i from '.$this->getEntityName().'  i')->getResult() as $entity) {
            $entity->checkAmounts();
            $em->persist($entity);
        }
        $em->flush();
        return $this;
    }


    public function findByItem(Item $item)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i')
            ->from($this->getEntityName(), 'i')
            ->join('i.items', 'ii')
            ->where('ii.id = ?1')
            ->setParameter(1, $item->getId());

        return $qb->getQuery()->getResult();
    }

    public function paginatedSearch(array $params, $limit = 50, $page = 1)
    {
        if (!$this->paginator) {
            throw new \RuntimeException('You have to set a paginator first using setPaginator() method');
        }

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->getEntityName(), 'i');
        $this->addPaginatedSearchSelects($qb);
        $this->applySearchParamsToQuery($params, $qb);

        return $this->paginator->paginate($qb->getQuery(), $page, $limit, [
            'defaultSortFieldName' => 'i.id',
            'defaultSortDirection' => 'desc',
        ]);
    }

    /**
     * There is no easy way to inject things into repositories yet.
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function getTotals(array $params)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->getEntityName(), 'i');
        $this->applySearchParamsToQuery($params, $qb);

        $qb->addSelect('SUM(i.gross_amount)');
        $qb->addSelect('SUM(i.paid_amount)');
        $qb->addSelect('SUM(i.gross_amount - i.paid_amount)');
        $qb->addSelect('SUM(i.net_amount)');
        $qb->addSelect('SUM(i.tax_amount)');
        if (!empty($params['tax'])) {
            // Tax's total cost.
            $qb->addSelect('SUM(it.unitary_cost * (tx.value/100))');
        }
        $qb->setMaxResults(1);

        $result = $qb->getQuery()->getSingleResult();

        // Transform to named array for easier access.
        $totals = [
            'gross' => $result[1],
            'paid' => $result[2],
            'due' => $result[3],
            'net' => $result[4],
            'tax' => $result[5],
        ];

        if (!empty($params['tax'])) {
            $totals['tax_' . $params['tax']] = isset($result[6]) ? $result[6] : 0;
        }

        return $totals;
    }

    protected function applySearchParamsToQuery(array $params, QueryBuilder $qb)
    {
        foreach ($params as $field => $value) {
            if ($value === null) {
                continue;
            }
            if ($field == 'terms') {
                $qb->join('i.series', 's');
                $terms = $qb->expr()->literal("%$value%");
                if ($this->getEntityName() == Invoice::class) {
                    $expr = $qb->expr()->orX(
                        $qb->expr()->like('i.number', $terms),
                        $qb->expr()->like('s.name', $terms),
                        $qb->expr()->like("CONCAT(s.value, i.number)", $terms)
                    );
                }
                else {
                    $expr = $qb->expr()->like('s.name', $terms);
                }

                $qb->andWhere($expr);
            } elseif ($field == 'date_from') {
                $qb->andWhere('i.issue_date >= :date_from');
                $qb->setParameter('date_from', $value);
            } elseif ($field == 'date_to') {
                $qb->andWhere('i.issue_date <= :date_to');
                $qb->setParameter('date_to', $value);
            } elseif ($field == 'status') {
                $qb->andWhere('i.status = :status');
                $qb->setParameter('status', $value);
            } elseif ($field == 'customer') {
                $customer = $qb->expr()->literal("%$value%");
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('i.customer_name', $customer),
                    $qb->expr()->like('i.customer_identification', $customer)
                ));
            } elseif ($field == 'series') {
                $qb->andWhere('i.series = :series');
                $qb->setParameter('series', $value);
            } elseif ($field == 'tax') {
                $qb->join('i.items', 'it');
                $qb->join('it.taxes', 'tx');
                $qb->andWhere('tx.id = :tax');
                $qb->setParameter('tax', $value);
            }
        }
    }

    protected function addPaginatedSearchSelects(QueryBuilder $qb)
    {
        // Select everything by default.
        $qb->select('i');
    }
}
