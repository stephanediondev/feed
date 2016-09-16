<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class FeedRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed');
        $query->from('ReaderselfCoreBundle:Feed', 'fed');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('fed.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $cacheDriver->setNamespace('readerself.feed.');
        $getQuery->setResultCacheDriver($cacheDriver);
        $getQuery->setResultCacheLifetime(86400);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed.id');
        $query->from('ReaderselfCoreBundle:Feed', 'fed');

        if(isset($parameters['errors']) == 1 && $parameters['errors']) {
            $date = new \Datetime();
            $query->andWhere('fed.id IN (SELECT IDENTITY(errors.feed) FROM ReaderselfCoreBundle:CollectionFeed AS errors WHERE errors.error IS NOT NULL AND errors.dateCreated > DATE_SUB(:date, 12, \'HOUR\'))');
            $query->setParameter(':date', $date);
        }

        if(isset($parameters['subscribed']) == 1 && $parameters['subscribed']) {
            $query->andWhere('fed.id IN (SELECT IDENTITY(subscribed.feed) FROM ReaderselfCoreBundle:Subscription AS subscribed WHERE subscribed.member = :member)');
            $query->setParameter(':member', $parameters['member']);
        }

        if(isset($parameters['not_subscribed']) == 1 && $parameters['not_subscribed']) {
            $query->andWhere('fed.id NOT IN (SELECT IDENTITY(not_subscribed.feed) FROM ReaderselfCoreBundle:Subscription AS not_subscribed WHERE not_subscribed.member = :member)');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('fed.title');
        $query->groupBy('fed.id');

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
