<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\MemberManager;

use Readerself\CoreBundle\Entity\ActionItem;
use Readerself\CoreBundle\Event\ActionItemEvent;

class PinboardListener
{
    protected $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function add(ActionItemEvent $actionItemEvent)
    {
        $actionItem = $actionItemEvent->getdata();

        if($actionItem->getAction()->getTitle() == 'star') {
            $this->query('add', $actionItem);
        }
    }

    public function delete(ActionItemEvent $actionItemEvent)
    {
        $actionItem = $actionItemEvent->getdata();

        if($actionItem->getAction()->getTitle() == 'star') {
            $this->query('delete', $actionItem);
        }
    }

    private function query($method, ActionItem $actionItem)
    {
        $member = $actionItem->getMember();

        if($connection = $this->memberManager->connectionManager->getOne(['type' => 'pinboard', 'member' => $member])) {
            $item = $actionItem->getItem();

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
