<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;
use Readerself\CoreBundle\Manager\FeedManager;

class ActionController extends AbstractController
{
    protected $feedManager;

    public function __construct(
        FeedManager $feedManager
    ) {
        $this->feedManager = $feedManager;
    }

    /**
     * Retrieve all actions.
     *
     * @ApiDoc(
     *     section="Action",
     *     headers={
     *         {"name"="X-AUTHORIZE-KEY","required"=true},
     *     },
     * )
     */
    public function indexAction(Request $request, ParameterBag $parameterBag)
    {
        $data = [];
        $data['actions'] = [];
        foreach($this->actionManager->getList() as $action) {
            $data['actions'][] = [
               'id' => $action->getId(),
               'title' => $action->getTitle(),
               'website' => $action->getWebsite(),
            ];
        }
        return new JsonResponse($data);
    }
}
