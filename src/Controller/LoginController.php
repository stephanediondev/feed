<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Entity\Member;
use App\Form\Type\LoginType;
use App\Helper\DeviceDetectorHelper;
use App\Helper\JwtHelper;
use App\Helper\MaxmindHelper;
use App\Manager\MemberManager;
use App\Manager\MemberPasskeyManager;
use App\Model\JwtPayloadModel;
use App\Model\LoginModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use lbuchs\WebAuthn\WebAuthn;
use Symfony\Bundle\SecurityBundle\Security;

class LoginController extends AbstractAppController
{
    private MemberManager $memberManager;

    private MemberPasskeyManager $memberPasskeyManager;

    private UserPasswordHasherInterface $passwordHasher;

    private bool $maxmindEnabled;

    private bool $ldapEnabled = false;

    private string $ldapServer = 'ldap://localhost';

    private int $ldapPort = 389;

    private int $ldapProtocol = 3;

    private string $ldapRootDn = 'cn=Manager,dc=my-domain,dc=com';

    private string $ldapRootPw = 'secret';

    private string $ldapBaseDn = 'dc=my-domain,dc=com';

    private string $ldapSearchUser = 'mail=[email]';

    private string $ldapSearchGroupAdmin = 'cn=admingroup';

    public function __construct(MemberManager $memberManager, MemberPasskeyManager $memberPasskeyManager, UserPasswordHasherInterface $passwordHasher, bool $maxmindEnabled, bool $ldapEnabled, string $ldapServer, int $ldapPort, int $ldapProtocol, string $ldapRootDn, string $ldapRootPw, string $ldapBaseDn, string $ldapSearchUser, string $ldapSearchGroupAdmin)
    {
        $this->memberManager = $memberManager;
        $this->memberPasskeyManager = $memberPasskeyManager;
        $this->passwordHasher = $passwordHasher;
        $this->maxmindEnabled = $maxmindEnabled;
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

    #[Route(path: '/login', name: 'login', methods: ['POST'], priority: 20)]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $login = new LoginModel();
        $form = $this->createForm(LoginType::class, $login);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $login->getEmail() && $login->getPassword()) {
            if ($this->ldapEnabled) {
                $ldapConnect = ldap_connect($this->ldapServer, $this->ldapPort);
                if ($ldapConnect) {
                    ldap_set_option($ldapConnect, LDAP_OPT_PROTOCOL_VERSION, $this->ldapProtocol);
                    ldap_set_option($ldapConnect, LDAP_OPT_REFERRALS, 0);
                    if (ldap_bind($ldapConnect, $this->ldapRootDn, $this->ldapRootPw)) {
                        $ldapSearch = ldap_search($ldapConnect, $this->ldapBaseDn, str_replace('[email]', $login->getEmail(), $this->ldapSearchUser), ['uid']);
                        if ($ldapSearch && $ldapSearch instanceof \LDAP\Result) {
                            $ldapGetEntries = ldap_get_entries($ldapConnect, $ldapSearch);
                            if ($ldapGetEntries && true === is_numeric($ldapGetEntries['count']) && 0 < $ldapGetEntries['count']) {
                                try {
                                    $ldapSearch2 = ldap_search($ldapConnect, $this->ldapBaseDn, $this->ldapSearchGroupAdmin, []);
                                    $ldapGetEntries2 = false;
                                    if ($ldapSearch2 && $ldapSearch2 instanceof \LDAP\Result) {
                                        $ldapGetEntries2 = ldap_get_entries($ldapConnect, $ldapSearch2);
                                    }

                                    if (true === isset($ldapGetEntries[0]['dn']) && ldap_bind($ldapConnect, $ldapGetEntries[0]['dn'], $login->getPassword())) {
                                        $member = $this->memberManager->getOne(['email' => $login->getEmail()]);

                                        if (!$member) {
                                            $member = new Member();
                                            $member->setEmail($login->getEmail());
                                        }
                                        $member->setPassword($this->passwordHasher->hashPassword($member, $login->getPassword()));

                                        $administrator = false;
                                        if ($ldapGetEntries2) {
                                            if (true === isset($ldapGetEntries[0]['uid'][0]) && true === isset($ldapGetEntries2[0]['memberuid']) && in_array($ldapGetEntries[0]['uid'][0], $ldapGetEntries2[0]['memberuid'])) {
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

                    $extraFields = DeviceDetectorHelper::asArray($request);

                    if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                        $data = MaxmindHelper::get($extraFields['ip']);
                        $extraFields = array_merge($extraFields, $data);
                    }

                    $connection = new Connection();
                    $connection->setMember($member);
                    $connection->setType(Connection::TYPE_LOGIN);
                    $connection->setToken($identifier);
                    $connection->setExtraFields($extraFields);

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

        return $this->jsonResponse($data, $status);
    }

    #[Route(path: '/passkey/options', name: 'passkey_options', methods: ['GET'])]
    public function passkeyBegin(Request $request): JsonResponse
    {
        $data = [];
        $status = JsonResponse::HTTP_OK;

        $session = $request->getSession();

        $rpId = $request->getHost();
        $webAuthn = new WebAuthn('WebAuthn Library', $rpId);

        $ids = [];
        $data = $webAuthn->getGetArgs($ids, 60*4, true, true, true, true, true, false);

        // save challange to session. you have to deliver it to processGet later.
        $session->set('challenge', $webAuthn->getChallenge());

        return new JsonResponse($data, $status);
    }

    #[Route(path: '/passkey/login', name: 'passkey_login', methods: ['POST'])]
    public function passkeyFinish(Request $request, Security $security): JsonResponse
    {
        $data = [];
        $status = JsonResponse::HTTP_OK;

        $content = $this->getContent($request);

        $memberPasskey = $this->memberPasskeyManager->getOne(['credential_id' => $content['id']]);

        if (null !== $memberPasskey) {
            $member = $memberPasskey->getMember();

            $session = $request->getSession();

            $rpId = $request->getHost();
            $webAuthn = new WebAuthn('WebAuthn Library', $rpId);

            $clientDataJSON = base64_decode($content['clientDataJSON']);
            $authenticatorData = base64_decode($content['authenticatorData']);
            $signature = base64_decode($content['signature']);
            $userHandle = base64_decode($content['userHandle']);
            $id = $content['id'];
            $rawId = base64_decode($content['rawId']);
            $challenge = $session->get('challenge') ?? '';
            $credentialPublicKey = null;

            if ($memberPasskey->getCredentialId() === $id) {
                $credentialPublicKey = $memberPasskey->getPublicKey();
            }

            if ($credentialPublicKey === null) {
                throw new \Exception('Public Key for credential ID not found!');
            }

            // if we have resident key, we have to verify that the userHandle is the provided userId at registration
            /*if ($requireResidentKey && $userHandle !== hex2bin($reg->userId)) {
                throw new \Exception('userId doesnt match (is ' . bin2hex($userHandle) . ' but expect ' . $reg->userId . ')');
            }*/

            // process the get request. throws WebAuthnException if it fails
            $userVerification = 'preferred';
            $webAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null, $userVerification === 'required');

            $memberPasskey->setLastTimeActive(new \Datetime());
            $this->memberPasskeyManager->persist($memberPasskey);

            $identifier = JwtHelper::generateUniqueIdentifier();

            $extraFields = DeviceDetectorHelper::asArray($request);

            if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                $data = MaxmindHelper::get($extraFields['ip']);
                $extraFields = array_merge($extraFields, $data);
            }

            $connection = new Connection();
            $connection->setMember($member);
            $connection->setType(Connection::TYPE_LOGIN);
            $connection->setToken($identifier);
            $connection->setExtraFields($extraFields);

            $this->connectionManager->persist($connection);

            $data['entry'] = $connection->toArray();
            $data['entry_entity'] = 'connection';

            $jwtPayloadModel = new JwtPayloadModel();
            $jwtPayloadModel->setJwtId($identifier);
            $jwtPayloadModel->setAudience(strval($member->getId()));

            $data['entry']['token_signed'] = JwtHelper::createToken($jwtPayloadModel);

            $data['success'] = true;

            $status = JsonResponse::HTTP_OK;
        }

        return new JsonResponse($data, $status);
    }
}
