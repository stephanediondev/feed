<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ActionFeedMemberRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_fed_mbr', 'act', 'fed', 'mbr');
        $query->from('ReaderselfCoreBundle:ActionFeedMember', 'act_fed_mbr');
        $query->leftJoin('act_fed_mbr.action', 'act');
        $query->leftJoin('act_fed_mbr.feed', 'fed');
        $query->leftJoin('act_fed_mbr.member', 'mbr');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('act_fed_mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_fed_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed_mbr.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('act_fed_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_fed_mbr', 'act', 'fed', 'mbr');
        $query->from('ReaderselfCoreBundle:ActionFeedMember', 'act_fed_mbr');
        $query->leftJoin('act_fed_mbr.action', 'act');
        $query->leftJoin('act_fed_mbr.feed', 'fed');
        $query->leftJoin('act_fed_mbr.member', 'mbr');

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_fed_mbr.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('act_fed_mbr.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('act_fed_mbr.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('act_fed_mbr.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
