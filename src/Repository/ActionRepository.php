<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Action;
use App\Repository\AbstractRepository;

class ActionRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Action::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Action
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act');
        $query->from(Action::class, 'act');

        if (true === isset($parameters['id'])) {
            $query->andWhere('act.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['title'])) {
            $query->andWhere('act.title = :title');
            $query->setParameter(':title', $parameters['title']);
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
        $query->addSelect('act');
        $query->from(Action::class, 'act');

        if (true === isset($parameters['id'])) {
            $query->andWhere('act.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $query->groupBy('act.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Action $action, bool $flush = true): void
    {
        $this->getEntityManager()->persist($action);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Action $action, bool $flush = true): void
    {
        $this->getEntityManager()->remove($action);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
