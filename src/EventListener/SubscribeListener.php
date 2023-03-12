<?php

namespace App\EventListener;

use App\Manager\MemberManager;

use App\Entity\ActionFeed;
use App\Event\ActionFeedEvent;

class SubscribeListener
{
    protected MemberManager $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function unread(ActionFeedEvent $actionFeedEvent)
    {
        $actionFeed = $actionFeedEvent->getdata();

        if ($actionFeed->getAction()->getTitle() == 'subscribe') {
            $member = $actionFeed->getMember();
            $this->memberManager->syncUnread($member->getid());
        }
    }
}
