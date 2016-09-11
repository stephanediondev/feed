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
        if(!$validateToken = $this->validateToken($request)) {
            $data['error'] = true;
            return new JsonResponse($data, 403);
        }

        $data['error'] = false;

        $member = $this->memberManager->getOne(['id' => 1]);

        $push = $this->memberManager->pushManager->init();
        $push->setMember($member);
        $push->setEndpoint($request->request->get('endpoint'));
        $push->setPublicKey($request->request->get('public_key'));
        $push->setAuthenticationSecret($request->request->get('authentication_secret'));
        $push->setAgent($request->server->get('HTTP_USER_AGENT'));

        $push_id = $this->memberManager->pushManager->persist($push);

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
    public function deleteAction(Request $request)
    {
    }
}
