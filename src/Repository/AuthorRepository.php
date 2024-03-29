<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionAuthor;
use App\Entity\Author;
use App\Entity\Item;
use App\Repository\AbstractRepository;

class AuthorRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Author::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Author
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('aut');
        $query->from(Author::class, 'aut');

        if (true === isset($parameters['id'])) {
            $query->andWhere('aut.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['title'])) {
            $query->andWhere('aut.title = :title');
            $query->setParameter(':title', $parameters['title']);
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

        if (true === isset($parameters['trendy']) && $parameters['trendy']) {
            if (isset($parameters['member'])) {
                $exclude = 'AND aut.id NOT IN( SELECT author_id FROM action_author WHERE member_id = :member_id AND action_id = :action_id )';
            } else {
                $exclude = '';
            }

            $sql = <<<SQL
SELECT LOWER(aut.title) AS ref, aut.id AS id, COUNT(DISTINCT(itm.id)) AS count
FROM item AS itm
LEFT JOIN author AS aut ON aut.id = itm.author_id
WHERE itm.date >= :date_ref AND aut.title != '' $exclude
GROUP BY ref
ORDER BY count DESC
LIMIT 0,100
SQL;

            $date_ref = date('Y-m-d H:i:s', time() - 3600 * 24 * 30);

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('date_ref', $date_ref);
            if (isset($parameters['member'])) {
                $stmt->bindValue('member_id', $parameters['member']->getId());
                $stmt->bindValue('action_id', 5);
            }
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            return $results;
        }

        $query = $em->createQueryBuilder();
        $query->addSelect('aut.id');
        $query->from(Author::class, 'aut');

        if (true === isset($parameters['id'])) {
            $query->andWhere('aut.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['title'])) {
            $query->andWhere('aut.title LIKE :title');
            $query->setParameter(':title', $parameters['title']);
        }

        if (true === isset($parameters['excluded']) && $parameters['excluded']) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(act_aut.author)');
            $subQuery->from(ActionAuthor::class, 'act_aut');
            $subQuery->andWhere('act_aut.member = :member AND act_aut.action = 5');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('aut.id', $subQuery->getDQL()));
            $query->setParameter(':member', $parameters['member']);
        }

        if (true === isset($parameters['feed'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('IDENTITY(item.author)');
            $subQuery->from(Item::class, 'item');
            $subQuery->andWhere('item.feed = :feed');
            $subQuery->distinct();

            $query->andWhere($query->expr()->in('aut.id', $subQuery->getDQL()));
            $query->setParameter(':feed', $parameters['feed']);
        }

        if (true === isset($parameters['days'])) {
            $query->andWhere('aut.dateCreated > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        $query->addOrderBy($parameters['sortField'], $parameters['sortDirection']);
        $query->groupBy('aut.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Author $author, bool $flush = true): void
    {
        $this->getEntityManager()->persist($author);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Author $author, bool $flush = true): void
    {
        $this->getEntityManager()->remove($author);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
