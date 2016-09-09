<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\ItemManager;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\MemberManager;
use Readerself\CoreBundle\Manager\ActionManager;

class ItemController extends AbstractController
{
    protected $itemManager;

    protected $categoryManager;

    protected $memberManager;

    protected $actionManager;

    public function __construct(
        ItemManager $itemManager,
        CategoryManager $categoryManager,
        MemberManager $memberManager,
        ActionManager $actionManager
    ) {
        $this->itemManager = $itemManager;
        $this->categoryManager = $categoryManager;
        $this->memberManager = $memberManager;
        $this->actionManager = $actionManager;
    }

    public function dispatchAction(Request $request, $action, $id)
    {
        if(!$this->isGranted('ROLE_FEEDS')) {
            //return $this->displayError(403);
        }

        $parameterBag = new ParameterBag();

        if(null !== $id) {
            $item = $this->itemManager->getOne(['id' => $id]);
            if($item) {
                $parameterBag->set('item', $item);
            } else {
                return $this->displayError(404);
            }
        }

        switch ($action) {
            case 'index':
                return $this->indexAction($request, $parameterBag);
            case 'read':
                return $this->readAction($request, $parameterBag);
            case 'star':
                return $this->starAction($request, $parameterBag);
            case 'share':
                return $this->shareAction($request, $parameterBag);
            case 'readability':
                return $this->readabilityAction($request, $parameterBag);
            case 'email':
                return $this->emailAction($request, $parameterBag);
        }

        return $this->displayError(404);
    }

    public function indexAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $data['items'] = [];
        $index = 0;
        foreach($this->itemManager->getList(['member' => $this->memberManager->getOne(['id' => 1])]) as $item) {
            $categories = [];
            foreach($this->categoryManager->itemCategoryManager->getList(['item' => $item]) as $itemCategory) {
                $categories[] = $itemCategory->toArray();
            }

            $enclosures = [];
            foreach($this->itemManager->enclosureManager->getList(['item' => $item]) as $enclosure) {
                $enclosures[] = $enclosure->toArray();
            }

            $data['items'][$index] = $item->toArray();
            $data['items'][$index]['categories'] = $categories;
            $data['items'][$index]['enclosures'] = $enclosures;
            $index++;
        }
        return new JsonResponse($data);
    }

    public function readAction(Request $request, ParameterBag $parameterBag)
    {
        return $this->setAction('read', $request, $parameterBag);
    }

    public function starAction(Request $request, ParameterBag $parameterBag)
    {
        return $this->setAction('star', $request, $parameterBag);
    }

    public function shareAction(Request $request, ParameterBag $parameterBag)
    {
        return $this->setAction('share', $request, $parameterBag);
    }

    private function setAction($case, Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $parameterBag->set('member', $this->memberManager->getOne(['member' => 1]));
        $parameterBag->set('action', $this->actionManager->getOne(['title' => $case]));

        if($actionItemMember = $this->actionManager->actionItemMemberManager->getOne([
            'action' => $parameterBag->get('action'),
            'item' => $parameterBag->get('item'),
            'member' => $parameterBag->get('member'),
        ])) {
            $this->actionManager->actionItemMemberManager->remove($actionItemMember);
        } else {
            $actionItemMember = $this->actionManager->actionItemMemberManager->init();
            $actionItemMember->setAction($parameterBag->get('action'));
            $actionItemMember->setItem($parameterBag->get('item'));
            $actionItemMember->setMember($parameterBag->get('member'));

            $this->actionManager->actionItemMemberManager->persist($actionItemMember);
        }

        return new JsonResponse($data);
    }

    private function readabilityAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $parameterBag->set('action', $this->actionManager->getOne(['title' => 'readability']));

        if($actionItem = $this->actionManager->actionItemManager->getOne([
            'action' => $parameterBag->get('action'),
            'item' => $parameterBag->get('item'),
        ])) {
        } else {
            $actionItem = $this->actionManager->actionItemManager->init();
            $actionItem->setAction($parameterBag->get('action'));
            $actionItem->setItem($parameterBag->get('item'));

            $this->actionManager->actionItemManager->persist($actionItem);
        }

        $this->itemManager->getContentFull($parameterBag->get('item'));

        return new JsonResponse($data);
    }

    public function emailAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $parameterBag->set('action', $this->actionManager->getOne(['title' => 'email']));

        if($actionItem = $this->actionManager->actionItemManager->getOne([
            'action' => $parameterBag->get('action'),
            'item' => $parameterBag->get('item'),
        ])) {
        } else {
            $actionItem = $this->actionManager->actionItemManager->init();
            $actionItem->setAction($parameterBag->get('action'));
            $actionItem->setItem($parameterBag->get('item'));

            $this->actionManager->actionItemManager->persist($actionItem);
        }

        return new JsonResponse($data);
    }
}
