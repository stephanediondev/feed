<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class FeedCategoryRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed_cat', 'cat');
        $query->from('ReaderselfCoreBundle:FeedCategory', 'fed_cat');
        $query->leftJoin('fed_cat.feed', 'fed');
        $query->leftJoin('fed_cat.category', 'cat');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('cmp.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.feed_category.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed_cat', 'cat');
        $query->from('ReaderselfCoreBundle:FeedCategory', 'fed_cat');
        $query->leftJoin('fed_cat.feed', 'fed');
        $query->leftJoin('fed_cat.category', 'cat');

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('fed_cat.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['category']) == 1) {
            $query->andWhere('fed_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('cat.id NOT IN (SELECT IDENTITY(exclude.category) FROM ReaderselfCoreBundle:ActionCategoryMember AS exclude WHERE exclude.member = :member AND exclude.action = 5)');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('fed_cat.id');

        $getQuery = $query->getQuery();

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.feed_category.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery;
    }
}
