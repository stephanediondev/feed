<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Readerself\CoreBundle\Manager\MemberManager;
use Readerself\CoreBundle\Manager\ActionManager;
use Readerself\CoreBundle\Manager\FeedManager;
use Readerself\CoreBundle\Manager\ItemManager;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\AuthorManager;
use Readerself\CoreBundle\Manager\CollectionManager;
use Readerself\CoreBundle\Manager\SearchManager;

abstract class AbstractController extends Controller
{
    protected $memberManager;

    protected $actionManager;

    protected $feedManager;

    protected $itemManager;

    protected $categoryManager;

    protected $authorManager;

    protected $collectionManager;

    protected $searchManager;

    public function setMemberManager(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function setActionManager(
        ActionManager $actionManager
    ) {
        $this->actionManager = $actionManager;
    }

    public function setFeedManager(
        FeedManager $feedManager
    ) {
        $this->feedManager = $feedManager;
    }

    public function setItemManager(
        ItemManager $itemManager
    ) {
        $this->itemManager = $itemManager;
    }

    public function setCategoryManager(
        CategoryManager $categoryManager
    ) {
        $this->categoryManager = $categoryManager;
    }

    public function setAuthorManager(
        AuthorManager $authorManager
    ) {
        $this->authorManager = $authorManager;
    }

    public function setCollectionManager(
        CollectionManager $collectionManager
    ) {
        $this->collectionManager = $collectionManager;
    }

    public function setSearchManager(
        SearchManager $searchManager
    ) {
        $this->searchManager = $searchManager;
    }

    public function validateToken(Request $request, $type = 'login') {
        if($request->headers->get('X-CONNECTION-TOKEN') && $connection = $this->memberManager->connectionManager->getOne(['type' => $type, 'token' => $request->headers->get('X-CONNECTION-TOKEN')])) {
            return $connection->getMember();
        }
        return false;
    }
}
