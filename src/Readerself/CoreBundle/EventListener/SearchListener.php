<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\SearchManager;

use Readerself\CoreBundle\Event\AuthorEvent;
use Readerself\CoreBundle\Event\CategoryEvent;
use Readerself\CoreBundle\Event\FeedEvent;
use Readerself\CoreBundle\Event\ItemEvent;

class SearchListener
{
    protected $searchManager;

    public function __construct(
        SearchManager $searchManager
    ) {
        $this->searchManager = $searchManager;
    }

    public function removeAuthor(AuthorEvent $authorEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->searchManager->getIndex().'/author/'.$authorEvent->getdata()->getId();
        $this->searchManager->query($action, $path);
    }

    public function removeCategory(CategoryEvent $categoryEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->searchManager->getIndex().'/category/'.$categoryEvent->getdata()->getId();
        $this->searchManager->query($action, $path);
    }

    public function removeFeed(FeedEvent $feedEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->searchManager->getIndex().'/feed/'.$feedEvent->getdata()->getId();
        $this->searchManager->query($action, $path);
    }

    public function removeItem(ItemEvent $itemEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->searchManager->getIndex().'/item/'.$itemEvent->getdata()->getId();
        $this->searchManager->query($action, $path);
    }
}
