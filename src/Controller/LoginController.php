<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Entity\Member;
use App\Form\Type\LoginType;
use App\Helper\JwtHelper;
use App\Manager\MemberManager;
use App\Model\JwtPayloadModel;
use App\Model\LoginModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractAppController
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

    #[Route(path: '/login', name: 'login', methods: ['POST'], priority: 20)]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $login = new LoginModel();
        $form = $this->createForm(LoginType::class, $login);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
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
                                        if (isset($ldapGetEntries2)) {
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

                    $connection = new Connection();
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
}
