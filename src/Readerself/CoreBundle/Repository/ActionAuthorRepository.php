<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class ActionAuthorRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_aut', 'act', 'aut');
        $query->from('ReaderselfCoreBundle:ActionAuthor', 'act_aut');
        $query->leftJoin('act_aut.action', 'act');
        $query->leftJoin('act_aut.author', 'aut');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('act_aut.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_aut.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['author']) == 1) {
            $query->andWhere('act_aut.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_aut', 'act', 'aut');
        $query->from('ReaderselfCoreBundle:ActionAuthor', 'act_aut');
        $query->leftJoin('act_aut.action', 'act');
        $query->leftJoin('act_aut.author', 'aut');

        if(isset($parameters['action']) == 1) {
            $query->andWhere('act_aut.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if(isset($parameters['author']) == 1) {
            $query->andWhere('act_aut.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        $query->groupBy('act_aut.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
