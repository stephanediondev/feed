<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Entity\Login;
use Readerself\CoreBundle\Form\Type\LoginType;

class LoginController extends AbstractController
{
    /**
     * Login.
     *
     * @ApiDoc(
     *     section="Login",
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];

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

                    $data['entry'] = $connection->toArray();
                    $data['entry_entity'] = 'Connection';

                    $connection_id = $this->memberManager->connectionManager->persist($connection);
                }
            }
        }

        return new JsonResponse($data);
    }
}
