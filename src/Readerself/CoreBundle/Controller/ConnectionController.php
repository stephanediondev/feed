<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Entity\Login;
use Readerself\CoreBundle\Form\Type\LoginType;

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

        $status = 401;

        $login = new Login();
        $form = $this->createForm(LoginType::class, $login);

        $form->submit($request->request->all());

        if($form->isValid()) {
            $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

            if($member) {
                $encoder = $this->get('security.password_encoder');

                if($encoder->isPasswordValid($member, $login->getPassword())) {
                    $connection = $this->memberManager->connectionManager->init();
                    $connection->setMember($member);
                    $connection->setType('login');
                    $connection->setToken(base64_encode(random_bytes(50)));
                    $connection->setIp($request->getClientIp());
                    $connection->setAgent($request->server->get('HTTP_USER_AGENT'));

                    $this->memberManager->connectionManager->persist($connection);

                    $data['entry'] = $connection->toArray();
                    $data['entry_entity'] = 'connection';

                    $status = 200;
                }
            }
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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $member]);

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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $member]);

        if(!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

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
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['id' => $id, 'member' => $member]);

        if(!$connection) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->memberManager->connectionManager->remove($connection);

        return new JsonResponse($data);
    }
}
