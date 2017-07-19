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

class MemberController extends AbstractController
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

                $this->memberManager->persist($member);

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
}
