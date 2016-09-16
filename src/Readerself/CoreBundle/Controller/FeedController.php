<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\FeedManager;

use Readerself\CoreBundle\Form\Type\FeedType;

class FeedController extends AbstractController
{
    protected $feedManager;

    public function __construct(
        FeedManager $feedManager
    ) {
        $this->feedManager = $feedManager;
    }

    /**
     * Retrieve all feeds.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="order", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""100""", "description"="items per page"},
     *     },
     * )
     */
    public function indexAction(Request $request)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $paginator= $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(['widgetParameterName' => 'page']);
        $pagination = $paginator->paginate(
            $this->feedManager->getList(['member' => $member]),
            $page = $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 100)
        );

        $data['entries'] = [];
        $index = 0;
        foreach($pagination as $result) {
            $feed = $this->feedManager->getOne(['id' => $result['id']]);
            $subscription = $this->feedManager->subscriptionManager->getOne(['member' => $member, 'feed' => $feed]);

            $data['entries'][$index] = $feed->toArray();
            if($subscription) {
                $data['entries'][$index]['subscription'] = $subscription->toArray();
            }
            $index++;
        }
        $data['entries_entity'] = 'Feed';
        $data['entries_total'] = $pagination->getTotalItemCount();
        $data['entries_pages'] = $pages = $pagination->getPageCount();
        $data['entries_page_current'] = $page;
        $pagePrevious = $page - 1;
        if($pagePrevious >= 1) {
            $data['entries_page_previous'] = $pagePrevious;
        }
        $pageNext = $page + 1;
        if($pageNext <= $pages) {
            $data['entries_page_next'] = $pageNext;
        }
        return new JsonResponse($data);
    }

    /**
     * Create a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"="true"},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=false},
     *         {"name"="link", "dataType"="string", "required"=true},
     *         {"name"="website", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function createAction(Request $request)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->init());

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $id = $this->feedManager->persist($form->getData());

            $data['entry'] = $this->feedManager->getOne(['id' => $id])->toArray();
            $data['entry_entity'] = 'Feed';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Retrieve a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        $collections = [];
        foreach($this->feedManager->collectionFeedManager->getList(['feed' => $feed]) as $collection) {
            $collections[] = $collection->toArray();
        }

        $data['entry'] = $feed->toArray();
        $data['entry']['collections'] = $collections;
        $data['entry_entity'] = 'Feed';

        return new JsonResponse($data);
    }

    /**
     * Update a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"="true"},
     *     },
     *     parameters={
     *         {"name"="title", "dataType"="string", "required"=false},
     *         {"name"="link", "dataType"="string", "required"=true},
     *         {"name"="website", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function updateAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $form = $this->createForm(FeedType::class, $this->feedManager->getOne(['id' => $id]));

        $form->submit($request->request->all(), false);

        if($form->isValid()) {
            $this->feedManager->persist($form->getData());

            $data['entry'] = $this->feedManager->getOne(['id' => $id])->toArray();
            $data['entry_entity'] = 'Feed';

        } else {
            $errors = $form->getErrors(true);
            foreach($errors as $error) {
                $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Delete a feed.
     *
     * @ApiDoc(
     *     section="Feed",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     * )
     */
    public function deleteAction(Request $request, $id)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $feed = $this->feedManager->getOne(['id' => $id]);

        $data['entry'] = $feed;
        $data['entry_entity'] = 'Feed';

        $this->feedManager->remove($feed);

        return new JsonResponse($data);
    }
}
