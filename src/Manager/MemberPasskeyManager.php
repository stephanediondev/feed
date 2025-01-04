<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\MemberPasskey;
use App\Manager\AbstractManager;
use App\Repository\MemberPasskeyRepository;

class MemberPasskeyManager extends AbstractManager
{
    private MemberPasskeyRepository $memberPasskeyRepository;

    public function __construct(MemberPasskeyRepository $memberPasskeyRepository)
    {
        $this->memberPasskeyRepository = $memberPasskeyRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?MemberPasskey
    {
        return $this->memberPasskeyRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->memberPasskeyRepository->getList($parameters);
    }

    public function persist(MemberPasskey $memberPasskey): void
    {
        $this->memberPasskeyRepository->persist($memberPasskey);

        $this->clearCache();
    }

    public function remove(MemberPasskey $memberPasskey): void
    {
        $this->memberPasskeyRepository->remove($memberPasskey);

        $this->clearCache();
    }
}
