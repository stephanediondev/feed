<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Feed;
use Readerself\CoreBundle\Event\FeedEvent;

use Minishlink\WebPush\WebPush;

class PushManager extends AbstractManager
{
    protected $gcm;

    public function __construct(
        string $gcm
    ) {
        $this->gcm = $gcm;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Push')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Push')->getList($parameters);
    }

    public function persist($data)
    {
        if($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new FeedEvent($data, $mode);
        $this->eventDispatcher->dispatch('feed.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('feed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }

    public function send($push, $payload)
    {
        $apiKeys = array(
            'GCM' => $this->gcm,
        );

        echo $this->gcm;

        $webPush = new WebPush($apiKeys);

        $endPoint = $push->getEndpoint();
        $userPublicKey = $push->getPublicKey();
        $userAuthToken = $push->getAuthenticationSecret();

        $endPoint = 'https://android.googleapis.com/gcm/send/etKouRvDL80:APA91bG9bA6GkImfqUTH2pAjjREXazp_8EZ83CmF3YWx7f46qgCHTYQrE8KNreRv6ojb6SVY5112qiK-TEKcCtYUebQnA7KUD_pIB0WjWp8G078aXU4hTfWyqqJxbzh6fKFtWUOYTVS1';
        $userPublicKey = 'BCq3AAYzNzdc2xQ12cOir_CDDuOaTntQT-wDyav6AlD42oMyxBR-thzmo0vmZrqf0CJrcTmcF0mdd-gj6Biee6E=';
        $userAuthToken = 'wKWXJ_hIHa1-SjjQuUMmqg==';

        //$webPush->sendNotification($endPoint, null, null, null, true);
        $webPush->sendNotification($endPoint, $payload, $userPublicKey, $userAuthToken, true);

        $webPush->flush();
    }
}
