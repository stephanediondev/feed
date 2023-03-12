<?php

namespace App\Repository;

use App\Repository\AbstractRepository;
use App\Entity\Enclosure;

class EnclosureRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Enclosure::class;
    }

    public function getOne($parameters = [])
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

        if ($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.enclosure.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = [])
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

        if ($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.enclosure.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery;
    }
}
