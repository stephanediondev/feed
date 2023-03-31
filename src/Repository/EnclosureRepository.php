<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enclosure;
use App\Repository\AbstractRepository;

class EnclosureRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Enclosure::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Enclosure
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('enr');
        $query->from(Enclosure::class, 'enr');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('enr.id = :id');
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
        $query->addSelect('enr');
        $query->from(Enclosure::class, 'enr');

        if (isset($parameters['item']) == 1) {
            $query->andWhere('enr.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        $query->groupBy('enr.id');

        $getQuery = $query->getQuery();

        return $getQuery;
    }

    public function persist(Enclosure $enclosure, bool $flush = true): void
    {
        $this->getEntityManager()->persist($enclosure);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Enclosure $enclosure, bool $flush = true): void
    {
        $this->getEntityManager()->remove($enclosure);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
