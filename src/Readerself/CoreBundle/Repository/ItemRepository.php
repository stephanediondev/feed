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
            $query->andWhere('fed.id = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['author']) == 1) {
            $query->andWhere('aut.id = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if(isset($parameters['category']) == 1) {
            $query->leftJoin('itm.categories', 'itm_cat');
            $query->leftJoin('cat.id', 'cat');
            $query->andWhere('cat.id = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        if(isset($parameters['member']) == 1) {
            $query->leftJoin('fed.subscriptions', 'sub');
            $query->andWhere('sub.member = :member');
            $query->setParameter(':member', $parameters['member']);

            if(isset($parameters['folder']) == 1) {
                $query->leftJoin('sub.folder', 'flr');
                $query->andWhere('flr.id = :folder');
                $query->setParameter(':folder', $parameters['folder']);
            }

            if(isset($parameters['starred']) == 1 && $parameters['starred']) {
                $query->andWhere('itm.id IN (SELECT IDENTITY(starred.item) FROM ReaderselfCoreBundle:ActionItemMember AS starred WHERE starred.member = :member AND starred.action = 2)');
            }

            if(isset($parameters['shared']) == 1 && $parameters['shared']) {
                $query->andWhere('itm.id IN (SELECT IDENTITY(shared.item) FROM ReaderselfCoreBundle:ActionItemMember AS shared WHERE shared.member = :member AND shared.action = 3)');
            }

            if(isset($parameters['unread']) == 1 && $parameters['unread']) {
                $query->andWhere('itm.id NOT IN (SELECT IDENTITY(unread.item) FROM ReaderselfCoreBundle:ActionItemMember AS unread WHERE unread.member = :member AND unread.action = 1)');
            }

            if(isset($parameters['priority']) == 1 && $parameters['priority']) {
                $query->andWhere('sub.priority = 1');
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
        $query->setMaxResults(100);

        $getQuery = $query->getQuery();

        /*$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.item.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);*/

        return $getQuery->getResult();
    }
}
