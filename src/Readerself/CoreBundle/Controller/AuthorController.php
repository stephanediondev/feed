<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\AuthorManager;

class AuthorController extends AbstractController
{
    protected $categoryManager;

    public function __construct(
        AuthorManager $categoryManager
    ) {
        $this->categoryManager = $categoryManager;
    }

    /**
     * Create an author.
     *
     * @ApiDoc(
     *     section="Author",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$validateToken = $this->validateToken($request)) {
            $data['error'] = true;
            return new JsonResponse($data, 403);
        }

        return new JsonResponse($data);
    }

    /**
     * Retrieve an author.
     *
     * @ApiDoc(
     *     section="Author",
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
        $actions = $this->get('readerself_core_manager_action')->actionCategoryMemberManager->getList(['member' => $member, 'category' => $category]);

        $data['entry'] = $category->toArray();
        foreach($actions as $action) {
            $data['entry'][$action->getAction()->getTitle()] = true;
        }
        $data['entry_entity'] = 'category';

        return new JsonResponse($data);
    }

    /**
     * Update an author.
     *
     * @ApiDoc(
     *     section="Author",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];

        $data['id'] = $id;

        return new JsonResponse($data);
    }

    /**
     * Delete an author.
     *
     * @ApiDoc(
     *     section="Author",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $data = [];

        $data['id'] = $id;

        return new JsonResponse($data);
    }
}
