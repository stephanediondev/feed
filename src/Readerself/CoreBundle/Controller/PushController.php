<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

class PushController extends AbstractController
{
    /**
     * Create a push.
     *
     * @ApiDoc(
     *     section="Push",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="endpoint", "dataType"="string", "required"=true},
     *         {"name"="public_key", "dataType"="string", "required"=false},
     *         {"name"="authentication_secret", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $push = $this->memberManager->pushManager->getOne(['endpoint' => $request->request->get('endpoint'), 'member' => $memberConnected]);
        if(!$push) {
            $push = $this->memberManager->pushManager->init();
            $push->setMember($memberConnected);
            $push->setEndpoint($request->request->get('endpoint'));
            $push->setPublicKey($request->request->get('public_key'));
            $push->setAuthenticationSecret($request->request->get('authentication_secret'));
            $push->setIp($request->getClientIp());
            $push->setAgent($request->server->get('HTTP_USER_AGENT'));
        } else {
            $push->setPublicKey($request->request->get('public_key'));
            $push->setAuthenticationSecret($request->request->get('authentication_secret'));
            $push->setIp($request->getClientIp());
            $push->setAgent($request->server->get('HTTP_USER_AGENT'));
        }
        $push_id = $this->memberManager->pushManager->persist($push);

        $push = $this->memberManager->pushManager->getOne(['id' => $push_id, 'member' => $memberConnected]);
        $data['entry'] = $push->toArray();
        $data['entry_entity'] = 'push';

        return new JsonResponse($data);
    }

    /**
     * Retrieve a push.
     *
     * @ApiDoc(
     *     section="Push",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$validateToken = $this->validateToken($request)) {
            $data['error'] = true;
            return new JsonResponse($data, 403);
        }

        $push = $this->memberManager->pushManager->getOne(['id' => $id]);

        if(!$push) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $push->toArray();
        $data['entry_entity'] = 'push';

        return new JsonResponse($data);
    }

    /**
     * Update a push.
     *
     * @ApiDoc(
     *     section="Push",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$validateToken = $this->validateToken($request)) {
            $data['error'] = true;
            return new JsonResponse($data, 403);
        }

        $push = $this->memberManager->pushManager->getOne(['id' => $id]);

        if(!$push) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $push->toArray();
        $data['entry_entity'] = 'push';

        return new JsonResponse($data);
    }

    /**
     * Delete a push.
     *
     * @ApiDoc(
     *     section="Push",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $data = [];
        if(!$validateToken = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $push = $this->memberManager->pushManager->getOne(['id' => $id]);

        if(!$push) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $push->toArray();
        $data['entry_entity'] = 'push';

        $this->memberManager->pushManager->remove($push);

        return new JsonResponse($data);
    }
}
