<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class CollectionRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('col');
        $query->from('ReaderselfCoreBundle:Collection', 'col');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('col.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('col');
        $query->from('ReaderselfCoreBundle:Collection', 'col');

        $query->addOrderBy('col.id', 'DESC');
        $query->groupBy('col.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
