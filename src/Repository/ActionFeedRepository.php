<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionFeed;
use App\Repository\AbstractRepository;

class ActionFeedRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ActionFeed::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionFeed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_fed', 'act', 'fed', 'mbr');
        $query->from(ActionFeed::class, 'act_fed');
        $query->leftJoin('act_fed.action', 'act');
        $query->leftJoin('act_fed.feed', 'fed');
        $query->leftJoin('act_fed.member', 'mbr');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('act_fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_fed.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_fed.member = :member');
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
        $query->addSelect('act_fed', 'act', 'fed', 'mbr');
        $query->from(ActionFeed::class, 'act_fed');
        $query->leftJoin('act_fed.action', 'act');
        $query->leftJoin('act_fed.feed', 'fed');
        $query->leftJoin('act_fed.member', 'mbr');

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_fed.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['feeds']) == 1) {
            $query->andWhere('act_fed.feed IN (:feeds)');
            $query->setParameter(':feeds', $parameters['feeds']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_fed.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('act_fed.id');

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
