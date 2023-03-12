<?php

namespace App\Repository;

use App\Repository\AbstractRepository;
use App\Entity\Collection;

class CollectionRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Collection::class;
    }

    public function getOne($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('col');
        $query->from(Collection::class, 'col');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('col.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('col');
        $query->from(Collection::class, 'col');

        $query->addOrderBy('col.id', 'DESC');
        $query->groupBy('col.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Collection $collection, bool $flush = true): void
    {
        $this->getEntityManager()->persist($collection);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Collection $collection, bool $flush = true): void
    {
        $this->getEntityManager()->remove($collection);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
