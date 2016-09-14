<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

class FolderController extends AbstractController
{
    /**
     * Retrieve all folders.
     *
     * @ApiDoc(
     *     section="Folder",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $folders = $this->memberManager->folderManager->getList();

        $data['entries'] = [];
        foreach($folders as $folder) {
            $data['entries'][] = $folder->toArray();
        }
        $data['entries_entity'] = 'Folder';
        $data['entries_total'] = count($folders);
        return new JsonResponse($data);
    }

    /**
     * Create a folder.
     *
     * @ApiDoc(
     *     section="Folder",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=true},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }
    }

    /**
     * Retrieve a folder.
     *
     * @ApiDoc(
     *     section="Folder",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $data['entry'] = $this->memberManager->folderManager->getOne(['id' => $id])->toArray();
        $data['entry_entity'] = 'Folder';

        return new JsonResponse($data);
    }

    /**
     * Update a folder.
     *
     * @ApiDoc(
     *     section="Folder",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"="true"},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=true},
     *     },
     * )
     */
    public function updateAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }
    }

    /**
     * Delete a folder.
     *
     * @ApiDoc(
     *     section="Folder",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    private function deleteAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }
    }
}
