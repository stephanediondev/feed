<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Push;
use Readerself\CoreBundle\Event\PushEvent;

use Minishlink\WebPush\WebPush;

class PushManager extends AbstractManager
{
    protected $notification_gcm_api_key;

    protected $notification_vapid_subject;

    protected $notification_vapid_public_key;

    protected $notification_vapid_private_key;

    public function __construct(
        $notification_gcm_api_key,
        $notification_vapid_subject,
        $notification_vapid_public_key,
        $notification_vapid_private_key
    ) {
        $this->notification_gcm_api_key = $notification_gcm_api_key;
        $this->notification_vapid_subject = $notification_vapid_subject;
        $this->notification_vapid_public_key = $notification_vapid_public_key;
        $this->notification_vapid_private_key = $notification_vapid_private_key;
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
        $sql = 'SELECT psh.id, psh.item_id, psh.member_id,
            (SELECT COUNT(itm.id) FROM item AS itm
                WHERE itm.feed_id IN (SELECT subscribed.feed_id FROM action_feed_member AS subscribed WHERE subscribed.member_id = psh.member_id AND subscribed.action_id = 3)
                AND itm.id NOT IN (SELECT alreadyRead.item_id FROM action_item_member AS alreadyRead WHERE alreadyRead.member_id = psh.member_id AND alreadyRead.action_id IN(1,4))
                AND itm.id IN (SELECT unreadSaved.item_id FROM action_item_member AS unreadSaved WHERE unreadSaved.member_id = psh.member_id AND unreadSaved.action_id = 12)
            ) AS unread
        FROM push AS psh';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach($results as $result) {
            if($result['unread'] > 0) {

                $sql = 'SELECT itm.id AS item_id, itm.title AS item_title FROM item AS itm LEFT JOIN feed AS fed ON fed.id = itm.feed_id
                    WHERE itm.feed_id IN (SELECT subscribed.feed_id FROM action_feed_member AS subscribed WHERE subscribed.member_id = :member_id AND subscribed.action_id = 3)
                    AND itm.id NOT IN (SELECT alreadyRead.item_id FROM action_item_member AS alreadyRead WHERE alreadyRead.member_id = :member_id AND alreadyRead.action_id IN(1,4))
                    AND itm.id IN (SELECT unreadSaved.item_id FROM action_item_member AS unreadSaved WHERE unreadSaved.member_id = :member_id AND unreadSaved.action_id = 12)
                ORDER BY itm.date DESC LIMIT 0,7';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('member_id', $result['member_id']);
                $stmt->execute();
                $lastList = $stmt->fetchAll();

                $body = [];
                $u = 1;
                foreach($lastList as $last) {
                    if($u == 1) {
                        $last_item_id = $last['item_id'];
                        $title = html_entity_decode($last['item_title']);
                    } else {
                        $body[] = html_entity_decode($last['item_title']);
                    }
                    $u++;
                }

                //if($result['item_id'] != $last_item_id) {
                    $updatePush = [];
                    $updatePush['item_id'] = $last_item_id;
                    $updatePush['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');
                    $this->update('push', $updatePush, $result['id']);

                    $payload = json_encode([
                        'title' => $title,
                        'body' => implode("\r\n", $body),
                    ]);
                    $this->send($result['id'], $payload);
                //}
            }
        }
    }

    public function send($push_id, $payload)
    {
        $push = $this->getOne(['id' => $push_id]);

        $apiKeys = array(
            'GCM' => $this->notification_gcm_api_key,
            'VAPID' => array(
                'subject' => $this->notification_vapid_subject,
                'publicKey' => $this->notification_vapid_public_key,
                'privateKey' => $this->notification_vapid_private_key,
            ),
        );

        $webPush = new WebPush($apiKeys);

        $endPoint = $push->getEndpoint();
        $userPublicKey = $push->getPublicKey();
        $userAuthToken = $push->getAuthenticationSecret();

        $webPush->sendNotification($endPoint, $payload, $userPublicKey, $userAuthToken, true);

        $webPush->flush();
    }
}
