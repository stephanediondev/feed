<?php

namespace App\Manager;

use App\Entity\Member;
use App\Event\MemberEvent;
use App\Manager\AbstractManager;
use App\Manager\ConnectionManager;
use App\Repository\MemberRepository;

class MemberManager extends AbstractManager
{
    public MemberRepository $memberRepository;

    public $connectionManager;

    public function __construct(
        MemberRepository $memberRepository,
        ConnectionManager $connectionManager
    ) {
        $this->memberRepository = $memberRepository;
        $this->connectionManager = $connectionManager;
    }

    public function getOne($parameters = []): ?Member
    {
        return $this->memberRepository->getOne($parameters);
    }

    public function getList($parameters = [])
    {
        return $this->memberRepository->getList($parameters);
    }

    public function init()
    {
        return new Member();
    }

    public function persist($data)
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

    public function remove($data)
    {
        $event = new MemberEvent($data);
        $this->eventDispatcher->dispatch($event, MemberEvent::DELETED);

        $this->memberRepository->remove($data);

        $this->clearCache();
    }

    public function syncUnread($member_id)
    {
        $this->memberRepository->syncUnread($member_id);
    }

    public function countUnread($member_id)
    {
        return $this->memberRepository->countUnread($member_id);
        ;
    }
}
