<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Item;
use App\Event\ItemEvent;
use App\Manager\AbstractManager;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Request;

class ItemManager extends AbstractManager
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Item
    {
        return $this->itemRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->itemRepository->getList($parameters);
    }

    public function persist(Item $item): void
    {
        if ($item->getDateCreated() === null) {
            $eventName = ItemEvent::CREATED;
            $item->setDateCreated(new \Datetime());
        } else {
            $eventName = ItemEvent::UPDATED;
        }
        $item->setDateModified(new \Datetime());

        $this->itemRepository->persist($item);

        $event = new ItemEvent($item);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(Item $item): void
    {
        $event = new ItemEvent($item);
        $this->eventDispatcher->dispatch($event, ItemEvent::DELETED);

        $this->itemRepository->remove($item);

        $this->clearCache();
    }

    /**
     * @return array<mixed>
     */
    public function prepareEnclosures(Item $item, Request $request): array
    {
        $enclosures = [];
        $index_enclosures = 0;
        foreach ($item->getEnclosures() as $enclosure) {
            $src = $enclosure->getLink();
            if ($item->getContent() && $src && !strstr($item->getContent(), $src)) {
                $enclosures[$index_enclosures] = $enclosure->getJsonApiData();
                if (!$enclosure->isLinkSecure() && $enclosure->getTypeGroup() == 'image') {
                    $token = urlencode(base64_encode($src));
                    $enclosures[$index_enclosures]['attributes']['link'] = '/proxy?token='.$token;
                }
                $index_enclosures++;
            }
        }
        return $enclosures;
    }

    /**
     * @param array<mixed> $parameters
     */
    public function readAll(array $parameters = []): void
    {
        foreach ($this->itemRepository->getList($parameters)->getResult() as $result) {
            $sql = 'SELECT id FROM action_item WHERE member_id = :member_id AND item_id = :item_id AND action_id = :action_id';
            $stmt = $this->itemRepository->getConnection()->prepare($sql);
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
                $this->itemRepository->insert('action_item', $insertActionItem);

                $sql = 'DELETE FROM action_item WHERE action_id = :action_id AND item_id = :item_id AND member_id = :member_id';
                $stmt = $this->itemRepository->getConnection()->prepare($sql);
                $stmt->bindValue('action_id', 12);
                $stmt->bindValue('item_id', $result['id']);
                $stmt->bindValue('member_id', $parameters['member']->getId());
                $stmt->executeQuery();
            }
        }
    }
}
