<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class MemberRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('mbr');
        $query->from('ReaderselfCoreBundle:Member', 'mbr');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['email']) == 1) {
            $query->andWhere('mbr.email = :email');
            $query->setParameter(':email', $parameters['email']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('fed');
        $query->from('ReaderselfCoreBundle:Member', 'mbr');

        $query->addOrderBy('mbr.email');
        $query->groupBy('mbr.id');

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
