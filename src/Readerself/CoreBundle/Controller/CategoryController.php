<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\ActionManager;

class CategoryController extends AbstractController
{
    protected $categoryManager;

    protected $actionManager;

    public function __construct(
        CategoryManager $categoryManager,
        ActionManager $actionManager
    ) {
        $this->categoryManager = $categoryManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Retrieve a category.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    /**
     * Set "exclude" action / Remove "exclude" action.
     *
     * @ApiDoc(
     *     section="Category",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function excludeAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $category = $this->categoryManager->getOne(['id' => $id]);
        $action = $this->actionManager->getOne(['title' => 'exclude']);

        if($actionCategoryMember = $this->actionManager->actionCategoryMemberManager->getOne([
            'action' => $action,
            'category' => $category,
            'member' => $member,
        ])) {
            $this->actionManager->actionCategoryMemberManager->remove($actionCategoryMember);
            $data['action'] = 'include';
        } else {
            $actionCategoryMember = $this->actionManager->actionCategoryMemberManager->init();
            $actionCategoryMember->setAction($action);
            $actionCategoryMember->setCategory($category);
            $actionCategoryMember->setMember($member);

            $this->actionManager->actionCategoryMemberManager->persist($actionCategoryMember);
            $data['action'] = 'exclude';
        }

        $data['entry'] = $category->toArray();
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }
}
