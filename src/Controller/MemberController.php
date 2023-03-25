<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\Type\LoginType;
use App\Form\Type\MemberType;
use App\Form\Type\PinboardType;
use App\Form\Type\ProfileType;
use App\Helper\JwtHelper;
use App\Manager\MemberManager;
use App\Model\JwtPayloadModel;
use App\Model\LoginModel;
use App\Model\PinboardModel;
use App\Model\ProfileModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_')]
class MemberController extends AbstractAppController
{
    private MemberManager $memberManager;

    private UserPasswordHasherInterface $passwordHasher;

    private bool $ldapEnabled = false;

    private string $ldapServer = 'ldap://localhost';

    private int $ldapPort = 389;

    private int $ldapProtocol = 3;

    private string $ldapRootDn = 'cn=Manager,dc=my-domain,dc=com';

    private string $ldapRootPw = 'secret';

    private string $ldapBaseDn = 'dc=my-domain,dc=com';

    private string $ldapSearchUser = 'mail=[email]';

    private string $ldapSearchGroupAdmin = 'cn=admingroup';

    public function __construct(MemberManager $memberManager, UserPasswordHasherInterface $passwordHasher, bool $ldapEnabled, string $ldapServer, int $ldapPort, int $ldapProtocol, string $ldapRootDn, string $ldapRootPw, string $ldapBaseDn, string $ldapSearchUser, string $ldapSearchGroupAdmin)
    {
        $this->memberManager = $memberManager;
        $this->passwordHasher = $passwordHasher;
        $this->ldapEnabled = $ldapEnabled;
        $this->ldapServer = $ldapServer;
        $this->ldapPort = $ldapPort;
        $this->ldapProtocol = $ldapProtocol;
        $this->ldapRootDn = $ldapRootDn;
        $this->ldapRootPw = $ldapRootPw;
        $this->ldapBaseDn = $ldapBaseDn;
        $this->ldapSearchUser = $ldapSearchUser;
        $this->ldapSearchGroupAdmin = $ldapSearchGroupAdmin;
    }

    public function create(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $member = $this->memberManager->init();
        $form = $this->createForm(MemberType::class, $member, ['validation_groups' => ['insert']]);

        $form->submit($request->request->all());

        $data[] = 'a';

        if ($form->isValid()) {
            $test = $this->memberManager->getOne(['email' => $member->getEmail()]);

            if (!$test) {
                $member->setPassword($this->passwordHasher->hashPassword($member, $member->getPassword()));

                $member_id = $this->memberManager->persist($member);

                $data[] = $form->getData()->getEmail();
            } else {
                $data[] = 'b';
                $status = JsonResponse::HTTP_FORBIDDEN;
            }
        }

        return new JsonResponse($data, $status);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $data['entry'] = $member->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($data);
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $login = new LoginModel();
        $form = $this->createForm(LoginType::class, $login);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            if ($this->ldapEnabled) {
                $ldapConnect = ldap_connect($this->ldapServer, $this->ldapPort);
                if ($ldapConnect) {
                    ldap_set_option($ldapConnect, LDAP_OPT_PROTOCOL_VERSION, $this->ldapProtocol);
                    ldap_set_option($ldapConnect, LDAP_OPT_REFERRALS, 0);
                    if (ldap_bind($ldapConnect, $this->ldapRootDn, $this->ldapRootPw)) {
                        $ldapSearch = ldap_search($ldapConnect, $this->ldapBaseDn, str_replace('[email]', $login->getEmail(), $this->ldapSearchUser), ['uid']);
                        if ($ldapSearch) {
                            $ldapGetEntries = ldap_get_entries($ldapConnect, $ldapSearch);
                            if ($ldapGetEntries && true === is_numeric($ldapGetEntries['count']) && 0 < $ldapGetEntries['count']) {
                                try {
                                    $ldapSearch2 = ldap_search($ldapConnect, $this->ldapBaseDn, $this->ldapSearchGroupAdmin, []);
                                    if ($ldapSearch2) {
                                        $ldapGetEntries2 = ldap_get_entries($ldapConnect, $ldapSearch2);
                                    }

                                    if (ldap_bind($ldapConnect, $ldapGetEntries[0]['dn'], $login->getPassword())) {
                                        $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

                                        if (!$member) {
                                            $member = $this->memberManager->init();
                                            $member->setEmail($login->getEmail());
                                        }
                                        $member->setPassword($this->passwordHasher->hashPassword($member, $login->getPassword()));

                                        $administrator = false;
                                        if (isset($ldapGetEntries2)) {
                                            if (in_array($ldapGetEntries[0]['uid'][0], $ldapGetEntries2[0]['memberuid'])) {
                                                $administrator = true;
                                            }
                                        }
                                        $member->setAdministrator($administrator);
                                        $this->memberManager->persist($member);
                                    }
                                } catch (\Exception $e) {
                                }
                            }
                        }
                    }
                    ldap_unbind($ldapConnect);
                }
            }

            $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

            if ($member) {
                if ($this->passwordHasher->isPasswordValid($member, $login->getPassword())) {
                    $identifier = JwtHelper::generateUniqueIdentifier();

                    $connection = $this->connectionManager->init();
                    $connection->setMember($member);
                    $connection->setType('login');
                    $connection->setToken($identifier);
                    $connection->setIp($request->getClientIp());
                    $connection->setAgent($request->server->get('HTTP_USER_AGENT'));

                    $this->connectionManager->persist($connection);

                    $data['entry'] = $connection->toArray();
                    $data['entry_entity'] = 'connection';

                    $jwtPayloadModel = new JwtPayloadModel();
                    $jwtPayloadModel->setJwtId($identifier);
                    $jwtPayloadModel->setAudience(strval($member->getId()));

                    $data['entry']['token_signed'] = JwtHelper::createToken($jwtPayloadModel);

                    $status = JsonResponse::HTTP_OK;
                }
            }
        }

        return new JsonResponse($data, $status);
    }

    #[Route(path: '/profile', name: 'profile', methods: ['GET'])]
    public function profile(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

        if ($pinboard) {
            $data['pinboard'] = $pinboard->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    #[Route(path: '/profile/connections', name: 'profile_connections', methods: ['GET'])]
    public function profileConnections(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $connections = [];
        $index = 0;
        foreach ($this->connectionManager->getList(['member' => $memberConnected])->getResult() as $connection) {
            $connections[$index] = $connection->toArray();
            if ($connection->getIp() == $request->getClientIp()) {
                $connections[$index]['currentIp'] = true;
            }
            if ($connection->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
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

    public function profileUpdate(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $profile = new ProfileModel();
        $form = $this->createForm(ProfileType::class, $profile);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $memberConnected->setEmail($profile->getEmail());
            if ($profile->getPassword()) {
                $memberConnected->setPassword($this->passwordHasher->hashPassword($memberConnected, $profile->getPassword()));
            }

            $this->memberManager->persist($memberConnected);
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        try {
            $payloadjwtPayloadModel = JwtHelper::getPayload(str_replace('Bearer ', '', $request->headers->get('Authorization')));
            $token = $payloadjwtPayloadModel->getJwtId();

            if ($connection = $this->connectionManager->getOne(['type' => 'login', 'token' => $token])) {
                $data['entry'] = $connection->toArray();
                $data['entry_entity'] = 'connection';

                $this->connectionManager->remove($connection);
            }

        } catch (\Exception $e) {
            throw new AccessDeniedHttpException();
        }

        return new JsonResponse($data);
    }

    public function pinboard(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = new PinboardModel();
        $form = $this->createForm(PinboardType::class, $pinboard);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $connection = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

            if ($connection) {
                $connection->setToken($pinboard->getToken());
            } else {
                $connection = $this->connectionManager->init();
                $connection->setMember($memberConnected);
                $connection->setType('pinboard');
                $connection->setToken($pinboard->getToken());
                $connection->setIp($request->getClientIp());
                $connection->setAgent($request->server->get('HTTP_USER_AGENT'));
            }
            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }

        return new JsonResponse($data);
    }

    public function notifier(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $login = new LoginModel();
        $form = $this->createForm(LoginType::class, $login);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

            if ($member) {
                if ($this->passwordHasher->isPasswordValid($member, $login->getPassword())) {
                    $connection = $this->connectionManager->init();
                    $connection->setMember($member);
                    $connection->setType('notifier');
                    $connection->setToken(base64_encode(random_bytes(50)));
                    $connection->setIp($request->getClientIp());
                    $connection->setAgent($request->server->get('HTTP_USER_AGENT'));

                    $this->connectionManager->persist($connection);

                    $data['entry'] = $connection->toArray();
                    $data['entry_entity'] = 'connection';

                    $status = JsonResponse::HTTP_UNAUTHORIZED;
                }
            }
        }

        return new JsonResponse($data, $status);
    }

    public function unread(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request, 'notifier')) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $data['unread'] = $this->memberManager->countUnread($memberConnected->getId());

        return new JsonResponse($data);
    }
}
