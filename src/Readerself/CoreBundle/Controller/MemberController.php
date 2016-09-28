<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Form\Type\MemberType;

use Readerself\CoreBundle\Entity\Login;
use Readerself\CoreBundle\Form\Type\LoginType;

use Readerself\CoreBundle\Entity\Profile;
use Readerself\CoreBundle\Form\Type\ProfileType;

use Readerself\CoreBundle\Entity\Pinboard;
use Readerself\CoreBundle\Form\Type\PinboardType;

class MemberController extends AbstractController
{
    /**
     * Create a member.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        if(!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $status = 200;

        $member = $this->memberManager->init();
        $form = $this->createForm(MemberType::class, $member, ['validation_groups'=>['insert']]);

        $form->submit($request->request->all());

        $data[] = 'a';

        if($form->isValid()) {
            $test = $this->memberManager->getOne(['email' => $member->getEmail()]);

            if(!$test) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($member, $member->getPlainPassword());
                $member->setPassword($encoded);

                $member_id = $this->memberManager->persist($member);

                $data[] = $form->getData()->getEmail();
            } else {
                $data[] = 'b';
                $status = 403;
            }
        }

        return new JsonResponse($data, $status);
    }

    /**
     * Retrieve a member.
     *
     * @ApiDoc(
     *     section="Member",
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

        if(!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if(!$member) {
            return new JsonResponse($data, 404);
        }

        $data['entry'] = $member->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    /**
     * Update a member.
     *
     * @ApiDoc(
     *     section="Member",
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

        if(!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if(!$member) {
            return new JsonResponse($data, 404);
        }

        return new JsonResponse($data);
    }

    /**
     * Delete a member.
     *
     * @ApiDoc(
     *     section="Member",
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

        if(!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if(!$member) {
            return new JsonResponse($data, 404);
        }

        return new JsonResponse($data);
    }

    /**
     * Login.
     *
     * @ApiDoc(
     *     section="Member",
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function loginAction(Request $request)
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
     * Profile.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function profileAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $pinboard = $this->memberManager->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

        if($pinboard) {
            $data['pinboard'] = $pinboard->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    /**
     * Profile connections.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function profileConnectionsAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connections = [];
        foreach($this->memberManager->connectionManager->getList(['member' => $memberConnected]) as $connection) {
            $connections[] = $connection->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        $data['entries'] = $connections;
        $data['entries_entity'] = 'connection';

        return new JsonResponse($data);
    }

    /**
     * Profile notifications.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function profileNotificationsAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $notifications = [];
        foreach($this->memberManager->pushManager->getList(['member' => $memberConnected]) as $connection) {
            $notifications[] = $connection->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        $data['entries'] = $notifications;
        $data['entries_entity'] = 'push';

        return new JsonResponse($data);
    }

    /**
     * Profile update.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=false},
     *         {"name"="passwordConfirm", "dataType"="string", "format"="", "required"=false},
     *     },
     * )
     */
    public function profileUpdateAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $profile = new Profile();
        $form = $this->createForm(ProfileType::class, $profile);

        $form->submit($request->request->all());

        if($form->isValid()) {
            $memberConnected->setEmail($profile->getEmail());
            if($profile->getPassword()) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($memberConnected, $profile->getPassword());
                $memberConnected->setPassword($encoded);
            }

            $this->memberManager->persist($memberConnected);
        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        $connections = [];
        foreach($this->memberManager->connectionManager->getList(['member' => $memberConnected]) as $connection) {
            $connections[] = $connection->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry']['connections'] = $connections;
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    /**
     * Logout.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function logoutAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $connection = $this->memberManager->connectionManager->getOne(['type' => 'login', 'token' => $request->headers->get('X-CONNECTION-TOKEN'), 'member' => $memberConnected]);

        $data['entry'] = $connection->toArray();
        $data['entry_entity'] = 'connection';

        $this->memberManager->connectionManager->remove($connection);


        return new JsonResponse($data);
    }

    /**
     * Set Pinboard API token.
     *
     * @ApiDoc(
     *     section="Member",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="token", "dataType"="string", "required"=true},
     *     },
     * )
     */
    public function pinboardAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $pinboard = new Pinboard();
        $form = $this->createForm(PinboardType::class, $pinboard);

        $form->submit($request->request->all());

        if($form->isValid()) {
            $connection = $this->memberManager->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

            if($connection) {
                $connection->setToken($pinboard->getToken());
            } else {
                $connection = $this->memberManager->connectionManager->init();
                $connection->setMember($memberConnected);
                $connection->setType('pinboard');
                $connection->setToken($pinboard->getToken());
                $connection->setIp($request->getClientIp());
                $connection->setAgent($request->server->get('HTTP_USER_AGENT'));

            }
            $this->memberManager->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }
}
