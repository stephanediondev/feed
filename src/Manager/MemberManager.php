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

    public function persist(Member $member): ?int
    {
        if ($member->getDateCreated() == null) {
            $eventName = MemberEvent::CREATED;
            $member->setDateCreated(new \Datetime());
        } else {
            $eventName = MemberEvent::UPDATED;
        }
        $member->setDateModified(new \Datetime());

        $this->memberRepository->persist($member);

        $event = new MemberEvent($member);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $member->getId();
    }

    public function remove(Member $member): void
    {
        $event = new MemberEvent($member);
        $this->eventDispatcher->dispatch($event, MemberEvent::DELETED);

        $this->memberRepository->remove($member);

        $this->clearCache();
    }

    public function syncUnread(?int $member_id): void
    {
        if ($member_id) {
            $this->memberRepository->syncUnread($member_id);
        }
    }

    public function countUnread(int $member_id): int
    {
        return $this->memberRepository->countUnread($member_id);
    }
}
