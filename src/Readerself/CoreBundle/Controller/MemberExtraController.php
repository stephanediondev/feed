<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\Config\Definition\Exception\Exception;
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

class MemberExtraController extends AbstractController
{
    protected $ldapEnabled = false;

    protected $ldapServer = 'ldap://localhost';

    protected $ldapPort = 389;

    protected $ldapProtocol = 3;

    protected $ldapRootDn = 'cn=Manager,dc=my-domain,dc=com';

    protected $ldapRootPw = 'secret';

    protected $ldapBaseDn = 'dc=my-domain,dc=com';

    protected $ldapSearchUser = 'mail=[email]';

    protected $ldapSearchGroupAdmin = 'cn=admingroup';

    public function setLdap($enabled, $server, $port, $protocol, $rootDn, $rootPw, $baseDn, $searchUser, $searchGroupAdmin)
    {
        $this->ldapEnabled = $enabled;
        $this->ldapServer = $server;
        $this->ldapPort = $port;
        $this->ldapProtocol = $protocol;
        $this->ldapRootDn = $rootDn;
        $this->ldapRootPw = $rootPw;
        $this->ldapBaseDn = $baseDn;
        $this->ldapSearchUser = $searchUser;
        $this->ldapSearchGroupAdmin = $searchGroupAdmin;
    }

    /**
     * Test.
     *
     * @ApiDoc(
     *     section="Member",
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function testAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $this->memberManager->syncUnread($memberConnected->getId());

        $data['unread'] = $this->memberManager->countUnread($memberConnected->getId());

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
            if($this->ldapEnabled) {
                $ldapConnect = ldap_connect($this->ldapServer, $this->ldapPort);
                if($ldapConnect) {
                    ldap_set_option($ldapConnect, LDAP_OPT_PROTOCOL_VERSION, $this->ldapProtocol);
                    ldap_set_option($ldapConnect, LDAP_OPT_REFERRALS, 0);
                    if(ldap_bind($ldapConnect, $this->ldapRootDn, $this->ldapRootPw)) {
                        $ldapSearch = ldap_search($ldapConnect, $this->ldapBaseDn, str_replace('[email]', $login->getEmail(), $this->ldapSearchUser), ['uid']);
                        if($ldapSearch) {
                            $ldapGetEntries = ldap_get_entries($ldapConnect, $ldapSearch);
                            if($ldapGetEntries['count'] > 0) {
                                try {

                                    $ldapSearch2 = ldap_search($ldapConnect, $this->ldapBaseDn, $this->ldapSearchGroupAdmin, []);
                                    if($ldapSearch2) {
                                        $ldapGetEntries2 = ldap_get_entries($ldapConnect, $ldapSearch2);
                                    }

                                    if(ldap_bind($ldapConnect, $ldapGetEntries[0]['dn'], $login->getPassword())) {
                                        $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

                                        if(!$member) {
                                            $member = $this->memberManager->init();
                                            $member->setEmail($login->getEmail());
                                        }
                                        $encoder = $this->get('security.password_encoder');
                                        $encoded = $encoder->encodePassword($member, $login->getPassword());
                                        $member->setPassword($encoded);

                                        $administrator = false;
                                        if(isset($ldapGetEntries2)) {
                                            if(in_array($ldapGetEntries[0]['uid'][0], $ldapGetEntries2[0]['memberuid'])) {
                                                $administrator = true;
                                            }
                                        }
                                        $member->setAdministrator($administrator);
                                        $this->memberManager->persist($member);
                                    }
                                } catch(Exception $e) {
                                }
                            }
                        }
                    }
                    ldap_unbind($ldapConnect);
                }
            }

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
        $index = 0;
        foreach($this->memberManager->connectionManager->getList(['member' => $memberConnected])->getResult() as $connection) {
            $connections[$index] = $connection->toArray();
            if($connection->getIp() == $request->getClientIp()) {
                $connections[$index]['currentIp'] = true;
            }
            if($connection->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
                $connections[$index]['currentAgent'] = true;
            }
            $index++;
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
        $index = 0;
        foreach($this->memberManager->pushManager->getList(['member' => $memberConnected])->getResult() as $notification) {
            $notifications[$index] = $notification->toArray();
            if($notification->getIp() == $request->getClientIp()) {
                $notifications[$index]['currentIp'] = true;
            }
            if($notification->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
                $notifications[$index]['currentAgent'] = true;
            }
            $index++;
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

        $data['entry'] = $memberConnected->toArray();
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

    /**
     * Notifier.
     *
     * @ApiDoc(
     *     section="Member",
     *     parameters={
     *         {"name"="email", "dataType"="string", "format"="email", "required"=true},
     *         {"name"="password", "dataType"="string", "format"="", "required"=true},
     *     },
     * )
     */
    public function notifierAction(Request $request)
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
                    $connection->setType('notifier');
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
     * Get unread.
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
    public function unreadAction(Request $request)
    {
        $data = [];
        if(!$memberConnected = $this->validateToken($request, 'notifier')) {
            return new JsonResponse($data, 403);
        }

        $data['unread'] = $this->memberManager->countUnread($memberConnected->getId());

        return new JsonResponse($data);
    }
}
