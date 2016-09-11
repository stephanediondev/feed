<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\FeedManager;

class SubscriptionController extends AbstractController
{
    protected $feedManager;

    public function __construct(
        FeedManager $feedManager
    ) {
        $this->feedManager = $feedManager;
    }

    /**
     * Retrieve all subscriptions.
     *
     * @ApiDoc(
     *     section="Subscription",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"=true},
     *     },
     * )
     */
    public function indexAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $data['subscriptions'] = [];
        foreach($this->subscriptionManager->getList() as $subscription) {
            $data['subscriptions'][] = [
               'id' => $subscription->getId(),
               'title' => $subscription->getTitle(),
               'website' => $subscription->getWebsite(),
            ];
        }
        return new JsonResponse($data);
    }

    /**
     * Create a subscription.
     *
     * @ApiDoc(
     *     section="Subscription",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"="true"},
     *     },
     *     parameters={
     *         {"name"="feed_id", "dataType"="integer", "required"=true},
     *         {"name"="folder_id", "dataType"="integer", "required"=false},
     *         {"name"="priority", "dataType"="integer", "format"="1 or 0", "required"=false},
     *         {"name"="direction", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function createAction(Request $request, ParameterBag $parameterBag)
    {
    }

    /**
     * Retrieve a subscription.
     *
     * @ApiDoc(
     *     section="Subscription",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"=true},
     *     },
     * )
     */
    public function readAction(Request $request, ParameterBag $parameterBag)
    {
    }

    /**
     * Update a subscription.
     *
     * @ApiDoc(
     *     section="Subscription",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"="true"},
     *     },
     *     parameters={
     *         {"name"="folder_id", "dataType"="string", "required"=false},
     *         {"name"="priority", "dataType"="boolean", "required"=false},
     *         {"name"="direction", "dataType"="string", "required"=false},
     *     },
     * )
     */
    public function updateAction(Request $request, ParameterBag $parameterBag)
    {
    }

    /**
     * Delete a subscription.
     *
     * @ApiDoc(
     *     section="Subscription",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"=true},
     *     },
     * )
     */
    private function deleteAction(Request $request, ParameterBag $parameterBag)
    {
    }
}
