<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\AuthorEvent;
use App\Event\CategoryEvent;
use App\Event\FeedEvent;
use App\Event\ItemEvent;
use App\Manager\ItemManager;
use App\Manager\SearchManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    private SearchManager $searchManager;

    private ItemManager $itemManager;

    public function __construct(SearchManager $searchManager, ItemManager $itemManager)
    {
        $this->searchManager = $searchManager;
        $this->itemManager = $itemManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthorEvent::DELETED => 'removeAuthor',
            CategoryEvent::DELETED => 'removeCategory',
            FeedEvent::DELETED => 'removeFeed',
            ItemEvent::DELETED => 'removeItem',
        ];
    }

    public function removeAuthor(AuthorEvent $authorEvent): void
    {
        $path = '/'.$this->searchManager->getIndex().'_author/doc/'.$authorEvent->getAuthor()->getId();
        $this->searchManager->query('DELETE', $path);
    }

    public function removeCategory(CategoryEvent $categoryEvent): void
    {
        $path = '/'.$this->searchManager->getIndex().'_category/doc/'.$categoryEvent->getCategory()->getId();
        $this->searchManager->query('DELETE', $path);
    }

    public function removeFeed(FeedEvent $feedEvent): void
    {
        $parameters = [];
        $parameters['feed'] = (int) $feedEvent->getFeed()->getId();
        $parameters['sortField'] = 'itm.id';
        $parameters['sortDirection'] = 'ASC';
        foreach ($this->itemManager->getList($parameters)->getResult() as $item) {
            $path = '/'.$this->searchManager->getIndex().'_item/doc/'.$item['id'];
            $this->searchManager->query('DELETE', $path);
        }

        $path = '/'.$this->searchManager->getIndex().'_feed/doc/'.$feedEvent->getFeed()->getId();
        $this->searchManager->query('DELETE', $path);
    }

    public function removeItem(ItemEvent $itemEvent): void
    {
        $path = '/'.$this->searchManager->getIndex().'_item/doc/'.$itemEvent->getItem()->getId();
        $this->searchManager->query('DELETE', $path);
    }
}
