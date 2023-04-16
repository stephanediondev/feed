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

        if (true === isset($parameters['id'])) {
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
        $query->addSelect('enr', 'itm');
        $query->from(Enclosure::class, 'enr');
        $query->leftJoin('enr.item', 'itm');

        if (true === isset($parameters['id'])) {
            $query->andWhere('enr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['item'])) {
            $query->andWhere('enr.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if (true === isset($parameters['days'])) {
            $query->andWhere('itm.date > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        if (true === isset($parameters['type'])) {
            $query->andWhere('enr.type = :type');
            $query->setParameter(':type', $parameters['type']);
        }

        $query->groupBy('enr.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

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
