<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class AuthorRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('aut');
        $query->from('ReaderselfCoreBundle:Author', 'aut');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('aut.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['title']) == 1) {
            $query->andWhere('aut.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('itm', 'fed', 'aut');
        $query->from('ReaderselfCoreBundle:Item', 'itm');
        $query->leftJoin('itm.feed', 'fed');
        $query->leftJoin('itm.author', 'aut');

        if(isset($parameters['feed']) == 1) {
            $query->andWhere('itm.feed = :feed');
            $query->setParameter(':feed', $parameters['feed']);
        }

        if(isset($parameters['member']) == 1) {
            $query->leftJoin('fed.subscriptions', 'sub');
            $query->andWhere('sub.member = :author');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('itm.date', 'DESC');
        $query->groupBy('itm.id');
        $query->setMaxResults(20);

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
