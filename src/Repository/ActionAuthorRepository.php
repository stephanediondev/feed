<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionAuthor;
use App\Repository\AbstractRepository;

class ActionAuthorRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return ActionAuthor::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?ActionAuthor
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_aut', 'act', 'aut', 'mbr');
        $query->from(ActionAuthor::class, 'act_aut');
        $query->leftJoin('act_aut.action', 'act');
        $query->leftJoin('act_aut.author', 'aut');
        $query->leftJoin('act_aut.member', 'mbr');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('act_aut.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_aut.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['author']) == 1) {
            $query->andWhere('act_aut.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_aut.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        return $getQuery->getOneOrNullResult();
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('act_aut', 'act', 'aut', 'mbr');
        $query->from(ActionAuthor::class, 'act_aut');
        $query->leftJoin('act_aut.action', 'act');
        $query->leftJoin('act_aut.author', 'aut');
        $query->leftJoin('act_aut.member', 'mbr');

        if (isset($parameters['action']) == 1) {
            $query->andWhere('act_aut.action = :action');
            $query->setParameter(':action', $parameters['action']);
        }

        if (isset($parameters['author']) == 1) {
            $query->andWhere('act_aut.author = :author');
            $query->setParameter(':author', $parameters['author']);
        }

        if (isset($parameters['authors']) == 1) {
            $query->andWhere('act_aut.author IN (:authors)');
            $query->setParameter(':authors', $parameters['authors']);
        }

        if (isset($parameters['member']) == 1) {
            $query->andWhere('act_aut.member = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('act_aut.id');

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(ActionAuthor $actionAuthor, bool $flush = true): void
    {
        $this->getEntityManager()->persist($actionAuthor);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActionAuthor $actionAuthor, bool $flush = true): void
    {
        $this->getEntityManager()->remove($actionAuthor);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
