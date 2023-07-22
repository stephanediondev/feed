<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionFeed;
use App\Entity\CollectionFeed;
use App\Entity\Feed;
use App\Entity\FeedCategory;
use App\Entity\Item;
use App\Repository\AbstractRepository;

class FeedRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Feed::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Feed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed');
        $query->from(Feed::class, 'fed');

        if (true === isset($parameters['id'])) {
            $query->andWhere('fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['link'])) {
            $query->andWhere('fed.link = :link');
            $query->setParameter(':link', $parameters['link']);
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
        $query->addSelect('fed.id');
        $query->from(Feed::class, 'fed');

        if (true === isset($parameters['id'])) {
            $query->andWhere('fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['link'])) {
            $query->andWhere('fed.link LIKE :link');
            $query->setParameter(':link', $parameters['link']);
        }

        if (true === isset($parameters['witherrors']) && $parameters['witherrors']) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(col_fed.feed)');
            $subQuery->from(CollectionFeed::class, 'col_fed');
            $subQuery->andWhere('col_fed.error IS NOT NULL AND col_fed.dateCreated > DATE_SUB(:date, 12, \'HOUR\')');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('fed.id', $subQuery->getDQL()));
            $query->setParameter(':date', new \Datetime());
        }

        if (true === isset($parameters['subscribed']) && $parameters['subscribed']) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(act_fed.feed)');
            $subQuery->from(ActionFeed::class, 'act_fed');
            $subQuery->andWhere('act_fed.member = :member AND act_fed.action = 3');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('fed.id', $subQuery->getDQL()));
            $query->setParameter(':member', $parameters['member']);
        }

        if (true === isset($parameters['unsubscribed']) && $parameters['unsubscribed']) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(act_fed.feed)');
            $subQuery->from(ActionFeed::class, 'act_fed');
            $subQuery->andWhere('act_fed.member = :member AND act_fed.action = 3');
            $subQuery->distinct();

            $query->andWhere($query->expr()->notIn('fed.id', $subQuery->getDQL()));
            $query->setParameter(':member', $parameters['member']);
        }

        if (true === isset($parameters['category'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(fed_cat.feed)');
            $subQuery->from(FeedCategory::class, 'fed_cat');
            $subQuery->andWhere('fed_cat.category = :category');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('fed.id', $subQuery->getDQL()));
            $query->setParameter(':category', $parameters['category']);
        }

        if (true === isset($parameters['author'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(item.feed)');
            $subQuery->from(Item::class, 'item');
            $subQuery->andWhere('item.author = :author');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('fed.id', $subQuery->getDQL()));
            $query->setParameter(':author', $parameters['author']);
        }

        if (true === isset($parameters['days'])) {
            $query->andWhere('fed.dateCreated > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        $query->addOrderBy($parameters['sortField'], $parameters['sortDirection']);
        $query->groupBy('fed.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Feed $feed, bool $flush = true): void
    {
        $this->getEntityManager()->persist($feed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Feed $feed, bool $flush = true): void
    {
        $this->getEntityManager()->remove($feed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
