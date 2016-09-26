<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Push;
use Readerself\CoreBundle\Event\PushEvent;

use Minishlink\WebPush\WebPush;

class PushManager extends AbstractManager
{
    protected $gcm;

    public function __construct(
        $gcm
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

    public function init()
    {
        return new Push();
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

        $event = new PushEvent($data, $mode);
        $this->eventDispatcher->dispatch('Push.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new PushEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Push.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }

    public function start()
    {
        $sql = 'SELECT psh.id, psh.agent, psh.member_id,
            (SELECT COUNT(itm.id) FROM item AS itm WHERE
                itm.feed_id IN ( SELECT subscribe.feed_id FROM action_feed_member AS subscribe WHERE subscribe.member_id = psh.member_id AND subscribe.action_id = 3)
                AND itm.id NOT IN (SELECT unread.item_id FROM action_item_member AS unread WHERE unread.member_id = psh.member_id AND unread.action_id = 1) )
            AS unread
        FROM push AS psh';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach($results as $result) {
            if($result['unread'] > 0) {

                $sql = 'SELECT itm.title AS item_title, fed.title AS feed_title FROM item AS itm LEFT JOIN feed AS fed ON fed.id = itm.feed_id WHERE
                    itm.feed_id IN ( SELECT subscribe.feed_id FROM action_feed_member AS subscribe WHERE subscribe.member_id = :member_id AND subscribe.action_id = 3)
                    AND itm.id NOT IN (SELECT unread.item_id FROM action_item_member AS unread WHERE unread.member_id = :member_id AND unread.action_id = 1)
                ORDER BY itm.date DESC LIMIT 0,3';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('member_id', $result['member_id']);
                $stmt->execute();
                $lastList = $stmt->fetchAll();

                $body = [];
                foreach($lastList as $last) {
                    $body[] = html_entity_decode($last['item_title'].' ('.$last['feed_title'].')');
                }

                $payload = json_encode([
                    'title' => $result['unread'].' unread items',
                    'body' => implode("\r\n", $body),
                ]);
                $this->send($result['id'], $payload);
            }
        }
    }

    public function send($push_id, $payload)
    {
        $push = $this->getOne(['id' => $push_id]);

        $apiKeys = array(
            'GCM' => $this->gcm,
        );

        $webPush = new WebPush($apiKeys);

        $endPoint = $push->getEndpoint();
        $userPublicKey = $push->getPublicKey();
        $userAuthToken = $push->getAuthenticationSecret();

        $webPush->sendNotification($endPoint, $payload, $userPublicKey, $userAuthToken, true);

        $webPush->flush();
    }
}
