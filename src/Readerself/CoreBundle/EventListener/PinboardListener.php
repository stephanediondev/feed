<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\MemberManager;

use Readerself\CoreBundle\Entity\ActionItemMember;
use Readerself\CoreBundle\Event\ActionItemMemberEvent;

class PinboardListener
{
    protected $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function add(ActionItemMemberEvent $actionItemMemberEvent)
    {
        $actionItemMember = $actionItemMemberEvent->getdata();

        if($actionItemMember->getAction()->getTitle() == 'star') {
            $this->query('add', $actionItemMember);
        }
    }

    public function delete(ActionItemMemberEvent $actionItemMemberEvent)
    {
        $actionItemMember = $actionItemMemberEvent->getdata();

        if($actionItemMember->getAction()->getTitle() == 'star') {
            $this->query('delete', $actionItemMember);
        }
    }

    private function query($method, ActionItemMember $actionItemMember)
    {
        $member = $actionItemMember->getMember();

        if($connection = $this->memberManager->connectionManager->getOne(['type' => 'pinboard', 'member' => $member])) {
            $item = $actionItemMember->getItem();

            $url = 'https://api.pinboard.in/v1/posts/'.$method;

            $fields = [
                'auth_token' => $connection->getToken(),
                'url' => $item->getLink(),
                'description' => $item->getTitle(),
                'replace' => 'yes',
            ];

            $ci = curl_init();
            curl_setopt($ci, CURLOPT_URL, $url.'?'.http_build_query($fields));
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ci);
        }
    }
}
