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

        if (isset($parameters['id']) == 1) {
            $query->andWhere('fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['link']) == 1) {
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

        if (isset($parameters['id']) == 1) {
            $query->andWhere('fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['witherrors']) == 1 && $parameters['witherrors']) {
            $date = new \Datetime();
            $query->andWhere('fed.id IN (SELECT IDENTITY(errors.feed) FROM '.CollectionFeed::class.' AS errors WHERE errors.error IS NOT NULL AND errors.dateCreated > DATE_SUB(:date, 12, \'HOUR\'))');
            $query->setParameter(':date', $date);
        }

        if (isset($parameters['subscribed']) == 1 && $parameters['subscribed']) {
            $query->andWhere('fed.id IN (SELECT IDENTITY(subscribe.feed) FROM '.ActionFeed::class.' AS subscribe WHERE subscribe.member = :member AND subscribe.action = 3)');
            $query->setParameter(':member', $parameters['member']);
        }

        if (isset($parameters['unsubscribed']) == 1 && $parameters['unsubscribed']) {
            $query->andWhere('fed.id NOT IN (SELECT IDENTITY(subscribe.feed) FROM '.ActionFeed::class.' AS subscribe WHERE subscribe.member = :member AND subscribe.action = 3)');
            $query->setParameter(':member', $parameters['member']);
        }

        if (isset($parameters['category']) == 1) {
            $query->andWhere('fed.id IN (SELECT IDENTITY(category.feed) FROM '.FeedCategory::class.' AS category WHERE category.category = :category)');
            $query->setParameter(':category', $parameters['category']);
        }

        if (isset($parameters['author']) == 1) {
            $query->andWhere('fed.id IN (SELECT IDENTITY(item.feed) FROM '.Item::class.' AS item WHERE item.author = :author)');
            $query->setParameter(':author', $parameters['author']);
        }

        if (isset($parameters['days']) == 1) {
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
