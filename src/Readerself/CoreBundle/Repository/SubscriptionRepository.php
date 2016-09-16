<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class SubscriptionRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('sub', 'mbr', 'fed');
        $query->from('ReaderselfCoreBundle:Subscription', 'sub');
        $query->leftJoin('sub.member', 'mbr');
        $query->leftJoin('sub.feed', 'fed');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('sub.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('sub.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('sub.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.subscription.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('sub', 'mbr', 'fed');
        $query->from('ReaderselfCoreBundle:Subscription', 'sub');
        $query->leftJoin('sub.member', 'mbr');
        $query->leftJoin('sub.feed', 'fed');

        if(isset($parameters['member']) == 1) {
            $query->andWhere('sub.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('sub.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        $query->addOrderBy('fed.title', 'ASC');
        $query->groupBy('sub.id');

        $getQuery = $query->getQuery();

        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.subscription.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);

        return $getQuery->getResult();
    }
}
