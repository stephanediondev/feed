<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ActionFeedEvent;
use App\Manager\MemberManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscribeSubscriber implements EventSubscriberInterface
{
    private MemberManager $memberManager;

    public function __construct(MemberManager $memberManager)
    {
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

        if ($actionFeed->getAction() && $actionFeed->getAction()->getTitle() == 'subscribe') {
            if ($member = $actionFeed->getMember()) {
                $this->memberManager->syncUnread($member->getid());
            }
        }
    }
}
