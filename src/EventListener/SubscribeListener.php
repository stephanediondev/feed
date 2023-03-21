<?php

namespace App\EventListener;

use App\Event\ActionFeedEvent;
use App\Manager\MemberManager;

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
