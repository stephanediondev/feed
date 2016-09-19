<?php
namespace Readerself\CoreBundle\EventListener;

use Readerself\CoreBundle\Manager\SearchManager;
use Readerself\CoreBundle\Event\FeedEvent;

class SearchListener
{
    protected $searchManager;

    public function __construct(
        SearchManager $searchManager
    ) {
        $this->searchManager = $searchManager;
    }

    public function persist(FeedEvent $feedEvent)
    {
    }

    public function remove(FeedEvent $feedEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->searchManager->getIndex().'/feed/'.$feedEvent->getdata()->getId();
        $this->searchManager->query($action, $path);
    }
}
