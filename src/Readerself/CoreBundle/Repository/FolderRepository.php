<?php
namespace Readerself\CoreBundle\Repository;

use Readerself\CoreBundle\Repository\AbstractRepository;

class FolderRepository extends AbstractRepository
{
    public function getOne($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('flr', 'mbr');
        $query->from('ReaderselfCoreBundle:Folder', 'flr');
        $query->leftJoin('flr.member', 'mbr');

        if(isset($parameters['id']) == 1) {
            $query->andWhere('flr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if(isset($parameters['title']) == 1) {
            $query->andWhere('flr.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = []) {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('flr', 'mbr');
        $query->from('ReaderselfCoreBundle:Folder', 'flr');
        $query->leftJoin('flr.member', 'mbr');

        if(isset($parameters['member']) == 1) {
            $query->andWhere('sub.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->addOrderBy('flr.title', 'ASC');
        $query->groupBy('flr.id');

        $getQuery = $query->getQuery();

        return $getQuery->getResult();
    }
}
