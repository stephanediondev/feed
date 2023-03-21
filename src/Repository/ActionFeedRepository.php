<?php

namespace App\Repository;

use App\Entity\ActionFeed;
use App\Repository\AbstractRepository;

class ActionFeedRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ActionFeed::class;
    }

    public function getOne($parameters = []): ?ActionFeed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_fed_mbr', 'act', 'fed', 'mbr');
        $query->from(ActionFeed::class, 'act_fed_mbr');
        $query->leftJoin('act_fed_mbr.action', 'act');
        $query->leftJoin('act_fed_mbr.feed', 'fed');
        $query->leftJoin('act_fed_mbr.member', 'mbr');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('act_fed_mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_fed_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed_mbr.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_fed_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_fed_mbr', 'act', 'fed', 'mbr');
        $query->from(ActionFeed::class, 'act_fed_mbr');
        $query->leftJoin('act_fed_mbr.action', 'act');
        $query->leftJoin('act_fed_mbr.feed', 'fed');
        $query->leftJoin('act_fed_mbr.member', 'mbr');

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_fed_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed_mbr.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_fed_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('act_fed_mbr.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(ActionFeed $actionFeed, bool $flush = true): void
    {
        $this->getEntityManager()->persist($actionFeed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActionFeed $actionFeed, bool $flush = true): void
    {
        $this->getEntityManager()->remove($actionFeed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
