<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class CategoryRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('cat');
        $query->from('ReaderselfCoreBundle:Category', 'cat');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['title']) == 1) {
            $query->andWhere('cat.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.category.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('cat.id');
        $query->from('ReaderselfCoreBundle:Category', 'cat');

        if(isset($parameters['excluded']) == 1 && $parameters['excluded']) {
            $query->andWhere('cat.id IN (SELECT IDENTITY(excluded.category) FROM ReaderselfCoreBundle:ActionCategoryMember AS excluded WHERE excluded.member = :member AND excluded.action = 5)');
            $query->setParameter(':member', $parameters['member']);
        }

        if(isset($parameters['feed']) == 1 && $parameters['feed']) {
            $query->andWhere('cat.id IN (SELECT IDENTITY(feed.category) FROM ReaderselfCoreBundle:FeedCategory AS feed)');
        }

        $query->addOrderBy('cat.title');
        $query->groupBy('cat.id');

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
