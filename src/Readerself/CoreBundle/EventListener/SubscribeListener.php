<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\MemberManager;

use Readerself\CoreBundle\Entity\ActionFeedMember;
use Readerself\CoreBundle\Event\ActionFeedMemberEvent;

class SubscribeListener
{
    protected $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function unread(ActionFeedMemberEvent $actionFeedMemberEvent)
    {
        $actionFeedMember = $actionFeedMemberEvent->getdata();

        if($actionFeedMember->getAction()->getTitle() == 'subscribe') {
            $member = $actionFeedMember->getMember();
            $this->memberManager->syncUnread($member->getid());
        }
    }
}
