<?php

namespace App\EventSubscriber;

use App\Event\ActionFeedEvent;
use App\Manager\MemberManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscribeSubscriber implements EventSubscriberInterface
{
    protected MemberManager $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionFeedEvent::CREATED => 'unread',
            ActionFeedEvent::UPDATED => 'unread',
            ActionFeedEvent::DELETED => 'unread',
        ];
    }

    public function unread(ActionFeedEvent $actionFeedEvent): void
    {
        $actionFeed = $actionFeedEvent->getActionFeed();

        if ($actionFeed->getAction()->getTitle() == 'subscribe') {
            $member = $actionFeed->getMember();
            $this->memberManager->syncUnread($member->getid());
        }
    }
}
