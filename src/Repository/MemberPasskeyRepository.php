<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionCategory;
use App\Entity\MemberPasskey;
use App\Repository\AbstractRepository;

class MemberPasskeyRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return MemberPasskey::class;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?MemberPasskey
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('mbr_psk', 'mbr');
        $query->from(MemberPasskey::class, 'mbr_psk');
        $query->leftJoin('mbr_psk.member', 'mbr');

        if (true === isset($parameters['id'])) {
            $query->andWhere('mbr_psk.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['credential_id'])) {
            $query->andWhere('mbr_psk.credentialId = :credential_id');
            $query->setParameter(':credential_id', $parameters['credential_id']);
        }

        if (true === isset($parameters['member'])) {
            $query->andWhere('mbr.id = :member');
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
        $query->addSelect('mbr_psk', 'mbr');
        $query->from(MemberPasskey::class, 'mbr_psk');
        $query->leftJoin('mbr_psk.member', 'mbr');

        if (true === isset($parameters['id'])) {
            $query->andWhere('mbr_psk.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (true === isset($parameters['member'])) {
            $query->andWhere('mbr.id = :member');
            $query->setParameter(':member', $parameters['member']);
        }

        $query->groupBy('mbr_psk.id');

        $getQuery = $query->getQuery();

        return $getQuery;
    }

    public function persist(MemberPasskey $memberPasskey, bool $flush = true): void
    {
        $this->getEntityManager()->persist($memberPasskey);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MemberPasskey $memberPasskey, bool $flush = true): void
    {
        $this->getEntityManager()->remove($memberPasskey);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
