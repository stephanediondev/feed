<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ActionCategoryRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_cat', 'act', 'cat');
        $query->addSelect('act_cat');
        $query->from('ReaderselfCoreBundle:ActionCategory', 'act_cat');
        $query->leftJoin('act_cat.action', 'act');
        $query->leftJoin('act_cat.category', 'cat');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('act_cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_cat.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['category']) == 1) {
            $query->andWhere('act_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_cat', 'act', 'cat');
        $query->from('ReaderselfCoreBundle:ActionCategory', 'act_cat');
        $query->leftJoin('act_cat.action', 'act');
        $query->leftJoin('act_cat.category', 'cat');

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_cat.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['category']) == 1) {
            $query->andWhere('act_cat.category = :category');
            $query->setParameter(':category', $parameters['category']);
        }

        $query->groupBy('act_cat.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
