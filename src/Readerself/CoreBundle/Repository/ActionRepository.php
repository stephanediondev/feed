<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ActionRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act');
        $query->from('ReaderselfCoreBundle:Action', 'act');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('act.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['title']) == 1) {
            $query->andWhere('act.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.action.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act');
        $query->from('ReaderselfCoreBundle:Action', 'act');

        $query->groupBy('act.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
