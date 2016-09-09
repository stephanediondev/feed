<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ItemRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm', 'fed', 'aut');
        $query->from('ReaderselfCoreBundle:Item', 'itm');
        $query->leftJoin('itm.feed', 'fed');
        $query->leftJoin('itm.author', 'aut');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.item.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm', 'fed', 'aut');
        $query->from('ReaderselfCoreBundle:Item', 'itm');
        $query->leftJoin('itm.feed', 'fed');
        $query->leftJoin('itm.author', 'aut');

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('itm.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        $query->andWhere('fed.link LIKE \'%www.facebook.com%\'');

        $query->addOrderBy('itm.date', 'DESC');
        $query->groupBy('itm.id');
        $query->setMaxResults(20);

        $getQuery = $query->getQuery();

        /*$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.item.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);*/

        return $getQuery->getResult();
    }
}
