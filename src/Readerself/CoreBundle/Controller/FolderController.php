<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Form\Type\FolderType;

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

        $status = 200;

        $folder = $this->memberManager->folderManager->init();
        $form = $this->createForm(FolderType::class, $folder);

        $form->submit($request->request->all());

        if($form->isValid()) {
            $test = $this->memberManager->folderManager->getOne(['title' => $folder->getTitle()]);

            if(!$test) {
                $folder->setMember($member);
                $folder_id = $this->memberManager->folderManager->persist($folder);

                $data['entry'] = $this->memberManager->folderManager->getOne(['id' => $folder_id])->toArray();
                $data['entry_entity'] = 'Folder';
            } else {
                $status = 403;
            }
        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data, $status);
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
    public function readAction(Request $request, $id)
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
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FolderType::class, $this->memberManager->folderManager->getOne(['id' => $id]));

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $this->memberManager->folderManager->persist($form->getData());

            $data['entry'] = $this->memberManager->folderManager->getOne(['id' => $id])->toArray();
            $data['entry_entity'] = 'Folder';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
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
    public function deleteAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $folder = $this->memberManager->folderManager->getOne(['id' => $id]);

        $data['entry'] = $folder;
        $data['entry_entity'] = 'Folder';

        $this->memberManager->folderManager->remove($folder);

        return new JsonResponse($data);
    }
}
