<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\ItemManager;
use Readerself\CoreBundle\Entity\Feed;

class ItemController extends AbstractController
{
    protected $itemManager;

    public function __construct(
        ItemManager $itemManager
    ) {
        $this->itemManager = $itemManager;
    }

    public function dispatchAction(Request $request, $action, $id)
    {
        if(!$this->isGranted('ROLE_FEEDS')) {
            //return $this->displayError(403);
        }

        $parameterBag = new ParameterBag();

        if(null !== $id) {
            $language = $this->itemManager->getOne(['id' => $id]);
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
        $data['items'] = [];
        foreach($this->itemManager->getList() as $item) {
            $data['items'][] = [
               'id' => $item->getId(),
               'title' => $item->getTitle(),
               'content' => $item->getContent(),
            ];
        }
        return new JsonResponse($data);
    }

    public function createAction(Request $request, ParameterBag $parameterBag)
    {
        $language = new Language();
        $language->setIsActive(true);

        $form = $this->createForm(LanguageType::class, $language, [
            'language' => $language,
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $id = $this->itemManager->persist($form->getData());
                $this->addFlash('success', 'created');
                return $this->redirectToRoute('readerself_core_feed', ['action' => 'read', 'id' => $id]);
            }
        }

        $parameterBag->set('form', $form->createView());

        return $this->render('ReaderselfCoreBundle:Feed:create.html.twig', $parameterBag->all());
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
                $this->itemManager->persist($form->getData());
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
                $this->itemManager->remove($parameterBag->get('language'));
                $this->addFlash('success', 'deleted');
                return $this->redirectToRoute('readerself_core_feed', []);
            }
        }

        $parameterBag->set('form', $form->createView());

        return $this->render('ReaderselfCoreBundle:Feed:delete.html.twig', $parameterBag->all());
    }
}
