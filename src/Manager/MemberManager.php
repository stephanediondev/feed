<?php

namespace App\Manager;

use App\Entity\Member;
use App\Event\MemberEvent;
use App\Manager\AbstractManager;
use App\Repository\MemberRepository;

class MemberManager extends AbstractManager
{
    private MemberRepository $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Member
    {
        return $this->memberRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->memberRepository->getList($parameters);
    }

    public function init(): Member
    {
        return new Member();
    }

    public function persist(Member $data): int
    {
        if ($data->getDateCreated() == null) {
            $eventName = MemberEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = MemberEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->memberRepository->persist($data);

        $event = new MemberEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(Member $data): void
    {
        $event = new MemberEvent($data);
        $this->eventDispatcher->dispatch($event, MemberEvent::DELETED);

        $this->memberRepository->remove($data);

        $this->clearCache();
    }

    public function syncUnread(int $member_id): void
    {
        $this->memberRepository->syncUnread($member_id);
    }

    public function countUnread(int $member_id): int
    {
        return $this->memberRepository->countUnread($member_id);
    }
}
