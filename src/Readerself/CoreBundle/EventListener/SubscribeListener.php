<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\MemberManager;

use Readerself\CoreBundle\Entity\ActionFeed;
use Readerself\CoreBundle\Event\ActionFeedEvent;

class SubscribeListener
{
    protected $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function unread(ActionFeedEvent $actionFeedEvent)
    {
        $actionFeed = $actionFeedEvent->getdata();

        if($actionFeed->getAction()->getTitle() == 'subscribe') {
            $member = $actionFeed->getMember();
            $this->memberManager->syncUnread($member->getid());
        }
    }
}
