<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ItemCategoryRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm_cat', 'cat');
        $query->from('ReaderselfCoreBundle:ItemCategory', 'itm_cat');
        $query->leftJoin('itm_cat.item', 'itm');
        $query->leftJoin('itm_cat.category', 'cat');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('cmp.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.item_category.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm_cat', 'cat');
        $query->from('ReaderselfCoreBundle:ItemCategory', 'itm_cat');
        $query->leftJoin('itm_cat.item', 'itm');
        $query->leftJoin('itm_cat.category', 'cat');

        if(isset($parameters['item']) == 1) {
            $query->andWhere('itm_cat.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        if(isset($parameters['category']) == 1) {
            $query->andWhere('itm_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('cat.id NOT IN (SELECT IDENTITY(exclude.category) FROM ReaderselfCoreBundle:ActionCategoryMember AS exclude WHERE exclude.member = :member AND exclude.action = 5)');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('itm_cat.id');

        $getQuery = $query->getQuery();

        if($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.item_category.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery;
    }
}
