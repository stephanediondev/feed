<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\Item;
use App\Event\ItemEvent;
use App\Manager\EnclosureManager;
use App\Repository\ItemRepository;

class ItemManager extends AbstractManager
{
    public ItemRepository $itemRepository;

    public EnclosureManager $enclosureManager;

    public function __construct(ItemRepository $itemRepository, EnclosureManager $enclosureManager)
    {
        $this->itemRepository = $itemRepository;
        $this->enclosureManager = $enclosureManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->itemRepository->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->itemRepository->getList($parameters);
    }

    public function init()
    {
        return new Item();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $event = new ItemEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Item.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Item.before_remove');

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        $this->clearCache();
    }

    public function prepareEnclosures($item, $request)
    {
        $enclosures = [];
        $index_enclosures = 0;
        foreach ($this->enclosureManager->getList(['item' => $item])->getResult() as $enclosure) {
            $src = $enclosure->getLink();
            if (!strstr($item->getContent(), $src)) {
                $enclosures[$index_enclosures] = $enclosure->toArray();
                if (!$enclosure->isLinkSecure() && $request->server->get('HTTPS') == 'on' && $enclosure->getTypeGroup() == 'image') {
                    $token = urlencode(base64_encode($src));
                    $enclosures[$index_enclosures]['link'] = 'app/icons/icon-32x32.png';
                    $enclosures[$index_enclosures]['link_origin'] = $src;
                    $enclosures[$index_enclosures]['proxy'] = $this->router->generate('api_proxy', ['token' => $token], 0);
                }
                $index_enclosures++;
            }
        }
        return $enclosures;
    }

    public function readAll($parameters = [])
    {
        foreach ($this->itemRepository->getList($parameters)->getResult() as $result) {
            $sql = 'SELECT id FROM action_item WHERE member_id = :member_id AND item_id = :item_id AND action_id = :action_id';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('member_id', $parameters['member']->getId());
            $stmt->bindValue('item_id', $result['id']);
            $stmt->bindValue('action_id', 1);
            $resultSet = $stmt->executeQuery();
            $test = $resultSet->fetchAssociative();

            if ($test) {
            } else {
                $insertActionItem = [
                    'member_id' => $parameters['member']->getId(),
                    'item_id' => $result['id'],
                    'action_id' => 1,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_item', $insertActionItem);

                $sql = 'DELETE FROM action_item WHERE action_id = :action_id AND item_id = :item_id AND member_id = :member_id';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('action_id', 12);
                $stmt->bindValue('item_id', $result['id']);
                $stmt->bindValue('member_id', $parameters['member']->getId());
                $resultSet = $stmt->executeQuery();
            }
        }
    }
}
