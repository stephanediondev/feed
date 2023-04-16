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

        if (isset($parameters['id']) == 1) {
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

        if (isset($parameters['id']) == 1) {
            $query->andWhere('fed_cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('fed_cat.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['feeds']) == 1) {
            $query->andWhere('fed_cat.feed IN (:feeds)');
            $query->setParameter(':feeds', $parameters['feeds']);
        }

        if (isset($parameters['category']) == 1) {
            $query->andWhere('fed_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('cat.id NOT IN (SELECT IDENTITY(exclude.category) FROM '.ActionCategory::class.' AS exclude WHERE exclude.member = :member AND exclude.action = 5)');
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
