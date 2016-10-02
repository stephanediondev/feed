<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ActionItemRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_itm', 'act', 'itm');
        $query->from('ReaderselfCoreBundle:ActionItem', 'act_itm');
        $query->leftJoin('act_itm.action', 'act');
        $query->leftJoin('act_itm.item', 'itm');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('act_itm.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_itm.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['item']) == 1) {
            $query->andWhere('act_itm.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_itm', 'act', 'itm');
        $query->from('ReaderselfCoreBundle:ActionItem', 'act_itm');
        $query->leftJoin('act_itm.action', 'act');
        $query->leftJoin('act_itm.item', 'itm');

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_itm.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['item']) == 1) {
            $query->andWhere('act_itm.item = :item');
            $query->setParameter(':item', $parameters['item']);
        }

        $query->groupBy('act_itm.id');

        return $query->getQuery();
    }
}
