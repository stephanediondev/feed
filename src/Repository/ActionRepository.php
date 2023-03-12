<?php

namespace App\Repository;

use App\Repository\AbstractRepository;
use App\Entity\Action;

class ActionRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Action::class;
    }

    public function getOne($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act');
        $query->from(Action::class, 'act');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('act.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['title']) == 1) {
            $query->andWhere('act.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if ($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.action.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act');
        $query->from(Action::class, 'act');

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
