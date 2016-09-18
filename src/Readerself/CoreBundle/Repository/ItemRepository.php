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
        $query->addSelect('itm.id');
        $query->from('ReaderselfCoreBundle:Item', 'itm');
        $query->leftJoin('itm.feed', 'fed');

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('fed.id = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['author']) == 1) {
            $query->leftJoin('itm.author', 'aut');
            $query->andWhere('aut.id = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if(isset($parameters['category']) == 1) {
            $query->andWhere('itm.id IN (SELECT IDENTITY(category.item) FROM ReaderselfCoreBundle:ItemCategory AS category WHERE category.category = :category)');
            $query->setParameter(':category', $parameters['category']);
        }

        if(isset($parameters['member']) == 1) {
            if(isset($parameters['unread']) == 1 && $parameters['unread']) {
                $query->andWhere('fed.id IN (SELECT IDENTITY(subscribe.feed) FROM ReaderselfCoreBundle:ActionFeedMember AS subscribe WHERE subscribe.member = :member AND subscribe.action = 3)');
                $query->andWhere('itm.id NOT IN (SELECT IDENTITY(unread.item) FROM ReaderselfCoreBundle:ActionItemMember AS unread WHERE unread.member = :member AND unread.action = 1)');
                $query->setParameter(':member', $parameters['member']);
            }

            if(isset($parameters['starred']) == 1 && $parameters['starred']) {
                $query->andWhere('itm.id IN (SELECT IDENTITY(starred.item) FROM ReaderselfCoreBundle:ActionItemMember AS starred WHERE starred.member = :member AND starred.action = 2)');
                $query->setParameter(':member', $parameters['member']);
            }
        }

        if(isset($parameters['geolocation']) == 1 && $parameters['geolocation']) {
             $query->andWhere('itm.latitude IS NOT NULL');
             $query->andWhere('itm.longitude IS NOT NULL');
        }

        if(isset($parameters['order']) == 1 && $parameters['order'] == 'asc') {
            $query->addOrderBy('itm.date', 'ASC');
        } else {
            $query->addOrderBy('itm.date', 'DESC');
        }

        $query->groupBy('itm.id');

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
