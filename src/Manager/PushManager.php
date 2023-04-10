<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\ConnectionManager;
use App\Manager\MemberManager;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushManager
{
    private ConnectionManager $connectionManager;

    private MemberManager $memberManager;

    private string $vapidPublicKey;

    private string $vapidPrivateKey;

    public function __construct(ConnectionManager $connectionManager, MemberManager $memberManager, string $vapidPublicKey, string $vapidPrivateKey)
    {
        $this->connectionManager = $connectionManager;
        $this->memberManager = $memberManager;
        $this->vapidPublicKey = $vapidPublicKey;
        $this->vapidPrivateKey = $vapidPrivateKey;
    }

    public function sendNotifications(): void
    {
        if ($subscriptions = $this->connectionManager->getList(['type' => 'push'])->getResult()) {
            $apiKeys = [
                'VAPID' => [
                    'subject' => 'https://github.com/stephanediondev/feed',
                    'publicKey' => $this->vapidPublicKey,
                    'privateKey' => $this->vapidPrivateKey,
                ],
            ];

            $webPush = new WebPush($apiKeys);

            foreach ($subscriptions as $subscription) {
                $endpoint = $subscription->getToken();
                $publicKey = $subscription->getExtraField('public_key');
                $authenticationSecret = $subscription->getExtraField('authentication_secret');
                $contentEncoding = $subscription->getExtraField('content_encoding');

                if ($publicKey && $authenticationSecret && $contentEncoding) {
                    $payload = [
                        'countunread' => $this->memberManager->countUnread($subscription->getMember()->getId()),
                    ];

                    if ($json = json_encode($payload)) {
                        $subscription = Subscription::create([
                            'endpoint' => $endpoint,
                            'publicKey' => $publicKey,
                            'authToken' => $authenticationSecret,
                            'contentEncoding' => $contentEncoding,
                        ]);

                        $webPush->queueNotification($subscription, $json);
                    }
                }
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();

                if (false === $report->isSuccess() && true === $report->isSubscriptionExpired()) {
                    if ($connection = $this->connectionManager->getOne(['type' => 'push', 'token' => $endpoint])) {
                        $this->connectionManager->remove($connection);
                    }
                }
            }
        }
    }
}
