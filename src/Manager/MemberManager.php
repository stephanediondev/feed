<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\Member;
use App\Event\MemberEvent;
use App\Repository\MemberRepository;

use App\Manager\ConnectionManager;

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

    public function getOne($paremeters = [])
    {
        return $this->memberRepository->getOne($paremeters);
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
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->memberRepository->persist($data);

        $event = new MemberEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Member.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new MemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Member.before_remove');

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
