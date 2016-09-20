<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Item;
use Readerself\CoreBundle\Event\ItemEvent;

class ItemManager extends AbstractManager
{
    public $enclosureManager;

    protected $readabilityEnabled;

    protected $readabilityKey;

    public function __construct(
        EnclosureManager $enclosureManager
    ) {
        $this->enclosureManager = $enclosureManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Item')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Item')->getList($parameters);
    }

    public function init()
    {
        return new Item();
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

        $event = new ItemEvent($data, $mode);
        $this->eventDispatcher->dispatch('Item.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Item.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }

    public function readAll($parameters = [])
    {
        foreach($this->em->getRepository('ReaderselfCoreBundle:Item')->getList($parameters) as $result) {
            $sql = 'SELECT id FROM action_item_member WHERE member_id = :member_id AND item_id = :item_id AND action_id = :action_id';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('member_id', $parameters['member']->getId());
            $stmt->bindValue('item_id', $result['id']);
            $stmt->bindValue('action_id', 1);
            $stmt->execute();
            $test = $stmt->fetch();

            if($test) {
            } else {
                $insertActionItemMember = [
                    'member_id' => $parameters['member']->getId(),
                    'item_id' => $result['id'],
                    'action_id' => 1,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_item_member', $insertActionItemMember);
            }
        }
    }

    public function setReadability($enabled, $key)
    {
        $this->readabilityEnabled = $enabled;
        $this->readabilityKey = $key;
    }

    public function getContentFull(Item $item)
    {
        if($this->readabilityEnabled && !$item->getContentFull()) {
            $url = 'https://www.readability.com/api/content/v1/parser?url='.urlencode($item->getLink()).'&token='.$this->readabilityKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if(isset($result['error']) == 0) {
                $item->setContentFull($result['content']);
                $this->persist($item);
            }
        }
    }
}
