<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionCategory;
use App\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;

class ActionCategoryRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ActionCategory::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionCategory
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_cat', 'act', 'cat', 'mbr');
        $query->from(ActionCategory::class, 'act_cat');
        $query->leftJoin('act_cat.action', 'act');
        $query->leftJoin('act_cat.category', 'cat');
        $query->leftJoin('act_cat.member', 'mbr');

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
        $query->addSelect('act_cat', 'act', 'cat', 'mbr');
        $query->from(ActionCategory::class, 'act_cat');
        $query->leftJoin('act_cat.action', 'act');
        $query->leftJoin('act_cat.category', 'cat');
        $query->leftJoin('act_cat.member', 'mbr');

        $this->applyParameters($query, $parameters);

        $query->groupBy('act_cat.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(ActionCategory $actionCategory, bool $flush = true): void
    {
        $this->getEntityManager()->persist($actionCategory);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActionCategory $actionCategory, bool $flush = true): void
    {
        $this->getEntityManager()->remove($actionCategory);

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
            $query->andWhere('act_cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['action'])) {
            $query->andWhere('act_cat.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (true === isset($parameters['category'])) {
            $query->andWhere('act_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if (true === isset($parameters['categories'])) {
            $query->andWhere('act_cat.category IN (:categories)');
            $query->setParameter(':categories', $parameters['categories']);
        }

        if (true === isset($parameters['member'])) {
            $query->andWhere('act_cat.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }
    }
}
