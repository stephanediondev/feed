<?php
namespace Readerself\SearchBundle\EventListener;

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

    public function remove(FeedEvent $feedEvent)
    {
        $action = 'DELETE';
        $path = '/'.$this->getIndex().'/feed/'.$data->getId();
        $this->searchManager->query($action, $path);
    }
}
