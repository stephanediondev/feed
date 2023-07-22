<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionFeed;
use App\Entity\ActionItem;
use App\Entity\Item;
use App\Entity\ItemCategory;
use App\Repository\AbstractRepository;

class ItemRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Item::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Item
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm', 'fed', 'aut');
        $query->from(Item::class, 'itm');
        $query->leftJoin('itm.feed', 'fed');
        $query->leftJoin('itm.author', 'aut');

        if (true === isset($parameters['id'])) {
            $query->andWhere('itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
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
        $query->addSelect('itm.id');
        $query->from(Item::class, 'itm');

        if (true === isset($parameters['id'])) {
            $query->andWhere('itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['feed'])) {
            $query->andWhere('itm.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (true === isset($parameters['author'])) {
            $query->andWhere('itm.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if (true === isset($parameters['category'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(itm_cat.item)');
            $subQuery->from(ItemCategory::class, 'itm_cat');
            $subQuery->andWhere('itm_cat.category = :category');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('itm.id', $subQuery->getDQL()));
            $query->setParameter(':category', $parameters['category']);
        }

        if (true === isset($parameters['member']) && $parameters['member']) {
            $memberSet = false;

            if (true === isset($parameters['unread']) && $parameters['unread']) {
                $memberSet = true;

                $query->leftJoin('itm.actions', 'act_itm');
                $query->andWhere('act_itm.member = :member');
                $query->andWhere('act_itm.action = 12');

                $subQuery = $this->getEntityManager()->createQueryBuilder();
                $subQuery->select('IDENTITY(act_fed.feed)');
                $subQuery->from(ActionFeed::class, 'act_fed');
                $subQuery->andWhere('act_fed.member = :member AND act_fed.action = 3');
                $subQuery->distinct();

                $query->andWhere($query->expr()->in('itm.feed', $subQuery->getDQL()));
            }

            if (true === isset($parameters['starred']) && $parameters['starred']) {
                $memberSet = true;

                $subQuery = $this->getEntityManager()->createQueryBuilder();
                $subQuery->select('IDENTITY(act_itm.item)');
                $subQuery->from(ActionItem::class, 'act_itm');
                $subQuery->andWhere('act_itm.member = :member AND act_itm.action = 2');
                $subQuery->distinct();

                $query->andWhere($query->expr()->in('itm.id', $subQuery->getDQL()));
            }

            if (true === $memberSet) {
                $query->setParameter(':member', $parameters['member']);
            }
        }

        if (true === isset($parameters['geolocation']) && $parameters['geolocation']) {
            $query->andWhere('itm.latitude IS NOT NULL');
            $query->andWhere('itm.longitude IS NOT NULL');
        }

        if (true === isset($parameters['days'])) {
            $query->andWhere('itm.date > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        if (true === isset($parameters['age'])) {
            $query->andWhere('itm.date < :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        $query->addOrderBy($parameters['sortField'], $parameters['sortDirection']);
        $query->groupBy('itm.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Item $item, bool $flush = true): void
    {
        $this->getEntityManager()->persist($item);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Item $item, bool $flush = true): void
    {
        $this->getEntityManager()->remove($item);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
