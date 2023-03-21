<?php

namespace App\EventSubscriber;

use App\Entity\ActionItem;
use App\Event\ActionItemEvent;
use App\Manager\MemberManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PinboardSubscriber implements EventSubscriberInterface
{
    protected MemberManager $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionItemEvent::CREATED => 'add',
            ActionItemEvent::DELETED => 'delete',
        ];
    }

    public function add(ActionItemEvent $actionItemEvent): void
    {
        $actionItem = $actionItemEvent->getActionItem();

        if ($actionItem->getAction()->getTitle() == 'star') {
            $this->query('add', $actionItem);
        }
    }

    public function delete(ActionItemEvent $actionItemEvent): void
    {
        $actionItem = $actionItemEvent->getActionItem();

        if ($actionItem->getAction()->getTitle() == 'star') {
            $this->query('delete', $actionItem);
        }
    }

    private function query(string $method, ActionItem $actionItem): void
    {
        $member = $actionItem->getMember();

        if ($connection = $this->memberManager->connectionManager->getOne(['type' => 'pinboard', 'member' => $member])) {
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