<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CollectionFeed;
use App\Repository\AbstractRepository;

class CollectionFeedRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return CollectionFeed::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?CollectionFeed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('col_fed', 'fed', 'col');
        $query->from(CollectionFeed::class, 'col_fed');
        $query->leftJoin('col_fed.feed', 'fed');
        $query->leftJoin('col_fed.collection', 'col');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('col_fed.id = :id');
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
        $query->addSelect('col_fed', 'fed', 'col');
        $query->from(CollectionFeed::class, 'col_fed');
        $query->leftJoin('col_fed.feed', 'fed');
        $query->leftJoin('col_fed.collection', 'col');

        if (isset($parameters['feed']) == 1) {
            $query->andWhere('col_fed.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (isset($parameters['error_notnull']) == 1 && $parameters['error_notnull']) {
            $query->andWhere('col_fed.error IS NOT NULL');
        }

        $query->addOrderBy('col_fed.id', 'DESC');
        $query->groupBy('col_fed.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(CollectionFeed $collectionFeed, bool $flush = true): void
    {
        $this->getEntityManager()->persist($collectionFeed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CollectionFeed $collectionFeed, bool $flush = true): void
    {
        $this->getEntityManager()->remove($collectionFeed);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
