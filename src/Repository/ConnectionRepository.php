<?php

namespace App\Repository;

use App\Entity\Connection;
use App\Repository\AbstractRepository;

class ConnectionRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Connection::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Connection
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('cnt');
        $query->from(Connection::class, 'cnt');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('cnt.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('cnt.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        if (isset($parameters['type']) == 1) {
            $query->andWhere('cnt.type = :type');
            $query->setParameter(':type', $parameters['type']);
        }

        if (isset($parameters['token']) == 1) {
            if (str_starts_with($parameters['token'], 'Bearer ')) {
                $parameters['token'] = substr($parameters['token'], 7);
            }
            $query->andWhere('cnt.token = :token');
            $query->setParameter(':token', $parameters['token']);
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
        $query->addSelect('cnt', 'mbr');
        $query->from(Connection::class, 'cnt');
        $query->leftJoin('cnt.member', 'mbr');

        if (isset($parameters['member']) == 1) {
            $query->andWhere('cnt.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('cnt.dateModified', 'DESC');
        $query->groupBy('cnt.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Connection $connection, bool $flush = true): void
    {
        $this->getEntityManager()->persist($connection);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Connection $connection, bool $flush = true): void
    {
        $this->getEntityManager()->remove($connection);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
