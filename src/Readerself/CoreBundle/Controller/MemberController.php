<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\MemberManager;
use Readerself\CoreBundle\Entity\Member;
use Readerself\CoreBundle\Form\Type\MemberType;

class MemberController extends AbstractController
{
    protected $pushManager;

    protected $memberManager;

    public function __construct(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
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
    public function createAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];

        $member = $this->memberManager->init();
        $form = $this->createForm(MemberType::class, $member, ['validation_groups'=>['insert']]);

        $form->submit($request->request->all());

        $data[] = 'a';

        if($form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($member, $member->getPlainPassword());
            $member->setPassword($encoded);

            $member_id = $this->memberManager->persist($member);

            $data[] = $form->getData()->getEmail();
            $data[] = 'b';
        }

        return new JsonResponse($data);
    }
}
