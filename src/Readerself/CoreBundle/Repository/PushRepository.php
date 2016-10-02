<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class PushRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('psh');
        $query->from('ReaderselfCoreBundle:Push', 'psh');
        $query->leftJoin('psh.member', 'mbr');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('psh.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['endpoint']) == 1) {
            $query->andWhere('psh.endpoint = :endpoint');
            $query->setParameter(':endpoint', $parameters['endpoint']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('psh.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.push.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('psh');
        $query->from('ReaderselfCoreBundle:Push', 'psh');
        $query->leftJoin('psh.member', 'mbr');

        if(isset($parameters['member']) == 1) {
            $query->andWhere('psh.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('psh.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
