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

        if (isset($parameters['id']) == 1) {
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

        if (isset($parameters['id']) == 1) {
            $query->andWhere('itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('itm.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['author']) == 1) {
            $query->andWhere('itm.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if (isset($parameters['category']) == 1) {
            $query->andWhere('itm.id IN (SELECT IDENTITY(category.item) FROM '.ItemCategory::class.' AS category WHERE category.category = :category)');
            $query->setParameter(':category', $parameters['category']);
        }

        if (isset($parameters['member']) == 1 && $parameters['member']) {
            $memberSet = false;

            if (isset($parameters['unread']) == 1 && $parameters['unread']) {
                $memberSet = true;

                $query->leftJoin('itm.actions', 'act_itm');
                $query->andWhere('act_itm.member = :member');
                $query->andWhere('act_itm.action = 12');
                $query->andWhere('itm.feed IN (SELECT IDENTITY(subscribe.feed) FROM '.ActionFeed::class.' AS subscribe WHERE subscribe.member = :member AND subscribe.action = 3)');
                //$query->andWhere('itm.id NOT IN (SELECT IDENTITY(unread.item) FROM '.ActionItem::class.' AS unread WHERE unread.member = :member AND unread.action IN(1,4))');
            }

            if (isset($parameters['starred']) == 1 && $parameters['starred']) {
                $memberSet = true;

                $query->leftJoin('itm.actions', 'act_itm');
                $query->andWhere('act_itm.member = :member');
                $query->andWhere('act_itm.action = 2');
                //$query->andWhere('itm.id IN (SELECT IDENTITY(starred.item) FROM '.ActionItem::class.' AS starred WHERE starred.member = :member AND starred.action = 2)');
            }

            if (true === $memberSet) {
                $query->setParameter(':member', $parameters['member']);
            }
        }

        if (isset($parameters['geolocation']) == 1 && $parameters['geolocation']) {
            $query->andWhere('itm.latitude IS NOT NULL');
            $query->andWhere('itm.longitude IS NOT NULL');
        }

        if (isset($parameters['days']) == 1) {
            $query->andWhere('itm.date > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        if (isset($parameters['age']) == 1) {
            $query->andWhere('itm.date < :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        //$query->leftJoin('itm.enclosures', 'enr');
        //$query->andWhere('enr.type = \'application/pdf\'');
        //$query->andWhere('(enr.type LIKE \'video%\' OR enr.type LIKE \'audio%\' OR enr.type LIKE \'image%\')');

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
