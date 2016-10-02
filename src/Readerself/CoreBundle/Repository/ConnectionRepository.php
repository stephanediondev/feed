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

        if(isset($parameters['id']) == 1) {
            $query->andWhere('cnt.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['member']) == 1) {
            $query->andWhere('cnt.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        if(isset($parameters['type']) == 1) {
            $query->andWhere('cnt.type = :type');
            $query->setParameter(':type', $parameters['type']);
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
        $query->addSelect('cnt', 'mbr');
        $query->from('ReaderselfCoreBundle:Connection', 'cnt');
        $query->leftJoin('cnt.member', 'mbr');

        if(isset($parameters['member']) == 1) {
            $query->andWhere('cnt.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('cnt.dateModified', 'DESC');
        $query->groupBy('cnt.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
