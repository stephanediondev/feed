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
    public function indexAction(Request $request, ParameterBag $parameterBag)
    {
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
    public function createAction(Request $request, ParameterBag $parameterBag)
    {
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
    }
}
