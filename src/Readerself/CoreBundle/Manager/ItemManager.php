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

    public function setReadability($enabled, $key)
    {
        $this->readabilityEnabled = $enabled;
        $this->readabilityKey = $key;
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
        $this->eventDispatcher->dispatch('item.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch('item.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
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
