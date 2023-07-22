<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionCategory;
use App\Entity\ItemCategory;
use App\Repository\AbstractRepository;

class ItemCategoryRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ItemCategory::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ItemCategory
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm_cat', 'cat');
        $query->from(ItemCategory::class, 'itm_cat');
        $query->leftJoin('itm_cat.item', 'itm');
        $query->leftJoin('itm_cat.category', 'cat');

        if (true === isset($parameters['id'])) {
            $query->andWhere('itm_cat.id = :id');
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
        $query->addSelect('itm_cat', 'cat');
        $query->from(ItemCategory::class, 'itm_cat');
        $query->leftJoin('itm_cat.item', 'itm');
        $query->leftJoin('itm_cat.category', 'cat');

        if (true === isset($parameters['id'])) {
            $query->andWhere('itm_cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['item'])) {
            $query->andWhere('itm_cat.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if (true === isset($parameters['items'])) {
            $query->andWhere('itm_cat.item IN (:items)');
            $query->setParameter(':items', $parameters['items']);
        }

        if (true === isset($parameters['category'])) {
            $query->andWhere('itm_cat.category = :category');
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

        $query->groupBy('itm_cat.id');

        $getQuery = $query->getQuery();

        return $getQuery;
    }

    public function persist(ItemCategory $itemCategory, bool $flush = true): void
    {
        $this->getEntityManager()->persist($itemCategory);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ItemCategory $itemCategory, bool $flush = true): void
    {
        $this->getEntityManager()->remove($itemCategory);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
