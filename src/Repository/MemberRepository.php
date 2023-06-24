<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member;
use App\Repository\AbstractRepository;

class MemberRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Member::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Member
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('mbr');
        $query->from(Member::class, 'mbr');

        if (true === isset($parameters['id'])) {
            $query->andWhere('mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['email'])) {
            $query->andWhere('mbr.email = :email');
            $query->setParameter(':email', $parameters['email']);
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
        $query->addSelect('mbr');
        $query->from(Member::class, 'mbr');

        if (true === isset($parameters['id'])) {
            $query->andWhere('mbr.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        $query->addOrderBy('mbr.email');
        $query->groupBy('mbr.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

        $getQuery = $query->getQuery();
        return $getQuery;
    }

    public function persist(Member $member, bool $flush = true): void
    {
        $this->getEntityManager()->persist($member);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Member $member, bool $flush = true): void
    {
        $this->getEntityManager()->remove($member);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countUnread(int $member_id): int
    {
        $sql = 'SELECT COUNT(DISTINCT(itm.id)) AS total FROM item AS itm
            WHERE itm.feed_id IN (SELECT subscribed.feed_id FROM action_feed AS subscribed WHERE subscribed.member_id = :member_id AND subscribed.action_id = 3)
            AND itm.id NOT IN (SELECT alreadyRead.item_id FROM action_item AS alreadyRead WHERE alreadyRead.member_id = :member_id AND alreadyRead.action_id IN(1,4))';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('member_id', $member_id);
        $resultSet = $stmt->executeQuery();

        $fetch = $resultSet->fetchAssociative();

        return $fetch['total'] ?? 0;
    }
}
