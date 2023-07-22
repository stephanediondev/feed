<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionCategory;
use App\Entity\FeedCategory;
use App\Repository\AbstractRepository;

class FeedCategoryRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return FeedCategory::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?FeedCategory
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed_cat', 'cat');
        $query->from(FeedCategory::class, 'fed_cat');
        $query->leftJoin('fed_cat.feed', 'fed');
        $query->leftJoin('fed_cat.category', 'cat');

        if (true === isset($parameters['id'])) {
            $query->andWhere('fed_cat.id = :id');
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
        $query->addSelect('fed_cat', 'cat');
        $query->from(FeedCategory::class, 'fed_cat');
        $query->leftJoin('fed_cat.feed', 'fed');
        $query->leftJoin('fed_cat.category', 'cat');

        if (true === isset($parameters['id'])) {
            $query->andWhere('fed_cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['feed'])) {
            $query->andWhere('fed_cat.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (true === isset($parameters['feeds'])) {
            $query->andWhere('fed_cat.feed IN (:feeds)');
            $query->setParameter(':feeds', $parameters['feeds']);
        }

        if (true === isset($parameters['category'])) {
            $query->andWhere('fed_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if (true === isset($parameters['member'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(act_cat.category)');
            $subQuery->from(ActionCategory::class, 'act_cat');
            $subQuery->andWhere('act_cat.member = :member AND act_cat.action = 5');
            $subQuery->distinct();

            $query->andWhere($query->expr()->notIn('cat.id', $subQuery->getDQL()));
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('fed_cat.id');

        $getQuery = $query->getQuery();

        return $getQuery;
    }

    public function persist(FeedCategory $feedCategory, bool $flush = true): void
    {
        $this->getEntityManager()->persist($feedCategory);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FeedCategory $feedCategory, bool $flush = true): void
    {
        $this->getEntityManager()->remove($feedCategory);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
