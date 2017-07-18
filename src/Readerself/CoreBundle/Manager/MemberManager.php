<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Member;
use Readerself\CoreBundle\Event\MemberEvent;

use Readerself\CoreBundle\Manager\ConnectionManager;

class MemberManager extends AbstractManager
{
    public $connectionManager;

    public $pushManager;

    public function __construct(
        ConnectionManager $connectionManager,
        PushManager $pushManager
    ) {
        $this->connectionManager = $connectionManager;
        $this->pushManager = $pushManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Member')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Member')->getList($parameters);
    }

    public function init()
    {
        return new Member();
    }

    public function persist($data)
    {
        if($data->getDateCreated() === null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new MemberEvent($data, $mode);
        $this->eventDispatcher->dispatch('Member.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new MemberEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Member.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }

    public function syncUnread($member_id)
    {
        $sql = 'INSERT INTO action_item_member (item_id, member_id, action_id, date_created) SELECT itm.id, :member_id, :action_id, :date_created FROM item AS itm
            WHERE itm.feed_id IN (SELECT subscribed.feed_id FROM action_feed_member AS subscribed WHERE subscribed.member_id = :member_id AND subscribed.action_id = 3)
            AND itm.id NOT IN (SELECT alreadyRead.item_id FROM action_item_member AS alreadyRead WHERE alreadyRead.member_id = :member_id AND alreadyRead.action_id IN(1,4))
            AND itm.id NOT IN (SELECT unreadSaved.item_id FROM action_item_member AS unreadSaved WHERE unreadSaved.member_id = :member_id AND unreadSaved.action_id = 12)
        ';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('member_id', $member_id);
        $stmt->bindValue('action_id', 12);
        $stmt->bindValue('date_created', (new \Datetime())->format('Y-m-d H:i:s'));
        $stmt->execute();
    }

    public function countUnread($member_id)
    {
        $sql = 'SELECT COUNT(DISTINCT(itm.id)) AS total FROM item AS itm
            WHERE itm.feed_id IN (SELECT subscribed.feed_id FROM action_feed_member AS subscribed WHERE subscribed.member_id = :member_id AND subscribed.action_id = 3)
            AND itm.id NOT IN (SELECT alreadyRead.item_id FROM action_item_member AS alreadyRead WHERE alreadyRead.member_id = :member_id AND alreadyRead.action_id IN(1,4))
            AND itm.id IN (SELECT unreadSaved.item_id FROM action_item_member AS unreadSaved WHERE unreadSaved.member_id = :member_id AND unreadSaved.action_id = 12)
        ';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('member_id', $member_id);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result['total'];
    }
}
