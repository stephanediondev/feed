<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ConnectionRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('cnt');
        $query->from('ReaderselfCoreBundle:Connection', 'cnt');

        if(isset($parameters['core']) == 1) {
            $query->andWhere('cnt.core = :core');
            $query->setParameter(':core', $parameters['core']);
        }

        if(isset($parameters['token']) == 1) {
            $query->andWhere('cnt.token = :token');
            $query->setParameter(':token', $parameters['token']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm', 'fed', 'aut');
        $query->from('ReaderselfCoreBundle:Connection', 'itm');
        $query->leftJoin('itm.feed', 'fed');
        $query->leftJoin('itm.author', 'aut');

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('itm.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['member']) == 1) {
            $query->leftJoin('fed.subscriptions', 'sub');
            $query->andWhere('sub.member = :action');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('itm.date', 'DESC');
        $query->groupBy('itm.id');
        $query->setMaxResults(20);

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
