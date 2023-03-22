<?php

namespace App\Repository;

use App\Entity\ActionItem;
use App\Repository\AbstractRepository;

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
        $query->addSelect('act_itm_mbr', 'act', 'itm', 'mbr');
        $query->from(ActionItem::class, 'act_itm_mbr');
        $query->leftJoin('act_itm_mbr.action', 'act');
        $query->leftJoin('act_itm_mbr.item', 'itm');
        $query->leftJoin('act_itm_mbr.member', 'mbr');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('act_itm_mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_itm_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['item']) == 1) {
            $query->andWhere('act_itm_mbr.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_itm_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

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
        $query->addSelect('act_itm_mbr', 'act', 'itm', 'mbr');
        $query->from(ActionItem::class, 'act_itm_mbr');
        $query->leftJoin('act_itm_mbr.action', 'act');
        $query->leftJoin('act_itm_mbr.item', 'itm');
        $query->leftJoin('act_itm_mbr.member', 'mbr');

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_itm_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['item']) == 1) {
            $query->andWhere('act_itm_mbr.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_itm_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('act_itm_mbr.id');

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
}
