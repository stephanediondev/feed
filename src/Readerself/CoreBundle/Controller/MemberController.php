<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

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

    public function dispatchAction(Request $request, $action, $id)
    {
        if(!$this->isGranted('ROLE_FEEDS')) {
            //return $this->displayError(403);
        }

        $parameterBag = new ParameterBag();

        if(null !== $id) {
            $language = $this->pushManager->getOne(['id' => $id]);
            if($language) {
                $parameterBag->set('language', $language);
            } else {
                return $this->displayError(404);
            }
        }

        switch ($action) {
            case 'index':
                return $this->indexAction($request, $parameterBag);
            case 'create':
                return $this->createAction($request, $parameterBag);
            case 'read':
                return $this->readAction($request, $parameterBag);
            case 'update':
                return $this->updateAction($request, $parameterBag);
            case 'delete':
                return $this->deleteAction($request, $parameterBag);
        }

        return $this->displayError(404);
    }

    public function indexAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $data['feeds'] = [];
        foreach($this->pushManager->getList() as $feed) {
            $data['feeds'][] = [
               'id' => $feed->getId(),
               'title' => $feed->getTitle(),
               'website' => $feed->getWebsite(),
            ];
        }
        return new JsonResponse($data);
    }

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

    public function readAction(Request $request, ParameterBag $parameterBag)
    {
        return $this->render('ReaderselfCoreBundle:Feed:read.html.twig', $parameterBag->all());
    }

    public function updateAction(Request $request, ParameterBag $parameterBag)
    {
        $form = $this->createForm(LanguageType::class, $parameterBag->get('language'), [
            'language' => $parameterBag->get('language'),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $this->pushManager->persist($form->getData());
                $this->addFlash('success', 'updated');
                return $this->redirectToRoute('readerself_core_feed', ['action' => 'read', 'id' => $parameterBag->get('language')->getId()]);
            }
        }

        $parameterBag->set('form', $form->createView());

        return $this->render('ReaderselfCoreBundle:Feed:update.html.twig', $parameterBag->all());
    }

    public function deleteAction(Request $request, ParameterBag $parameterBag)
    {
        $form = $this->createForm(DeleteType::class, null, []);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $this->pushManager->remove($parameterBag->get('language'));
                $this->addFlash('success', 'deleted');
                return $this->redirectToRoute('readerself_core_feed', []);
            }
        }

        $parameterBag->set('form', $form->createView());

        return $this->render('ReaderselfCoreBundle:Feed:delete.html.twig', $parameterBag->all());
    }
}
