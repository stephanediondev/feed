<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionItem;
use App\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;

class ActionItemRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ActionItem::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionItem
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_itm', 'act', 'itm', 'mbr');
        $query->from(ActionItem::class, 'act_itm');
        $query->leftJoin('act_itm.action', 'act');
        $query->leftJoin('act_itm.item', 'itm');
        $query->leftJoin('act_itm.member', 'mbr');

        $this->applyParameters($query, $parameters);

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_itm', 'act', 'itm', 'mbr');
        $query->from(ActionItem::class, 'act_itm');
        $query->leftJoin('act_itm.action', 'act');
        $query->leftJoin('act_itm.item', 'itm');
        $query->leftJoin('act_itm.member', 'mbr');

        $this->applyParameters($query, $parameters);

        $query->groupBy('act_itm.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(ActionItem $actionItem, bool $flush = true): void
    {
        $this->getEntityManager()->persist($actionItem);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActionItem $actionItem, bool $flush = true): void
    {
        $this->getEntityManager()->remove($actionItem);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<mixed> $parameters
     */
    public function applyParameters(QueryBuilder $query, array $parameters): void
    {
        if (true === isset($parameters['id'])) {
            $query->andWhere('act_itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['action'])) {
            $query->andWhere('act_itm.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (true === isset($parameters['item'])) {
            $query->andWhere('act_itm.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if (true === isset($parameters['items'])) {
            $query->andWhere('act_itm.item IN (:items)');
            $query->setParameter(':items', $parameters['items']);
        }

        if (true === isset($parameters['member'])) {
            $query->andWhere('act_itm.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }
    }
}
