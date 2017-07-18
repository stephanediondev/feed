<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

class ConnectionController extends AbstractController
{
    /**
     * Create a connection.
     *
     * @ApiDoc(
     *     section="Connection",
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        return new JsonResponse($data, $status);
    }

    /**
     * Retrieve a connection.
     *
     * @ApiDoc(
     *     section="Connection",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if(!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        return new JsonResponse($data);
    }

    /**
     * Update a connection.
     *
     * @ApiDoc(
     *     section="Connection",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if(!$connection) {
            return new JsonResponse($data, 404);
        }

        $connection->setIp($request->getClientIp());
        $connection->setAgent($request->server->get('HTTP_USER_AGENT'));
        $connection_id = $this->memberManager->connectionManager->persist($connection);

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $data['applicationServerKey'] = $this->applicationServerKey;

        return new JsonResponse($data);
    }

    /**
     * Delete a connection.
     *
     * @ApiDoc(
     *     section="Connection",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $memberConnected]);

        if(!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->memberManager->connectionManager->remove($connection);

        return new JsonResponse($data);
    }
}
