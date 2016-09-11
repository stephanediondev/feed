<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Readerself\CoreBundle\Manager\MemberManager;

abstract class AbstractController extends Controller
{
    protected $memberManager;

    public function setMemberManager(
        MemberManager $memberManager
    ) {
        $this->memberManager = $memberManager;
    }

    public function validateToken(Request $request) {
        if($request->headers->get('X-CONNECTION-TOKEN') && $this->memberManager->connectionManager->getOne(['type' => 'core', 'token' => $request->headers->get('X-CONNECTION-TOKEN')])) {
            return true;
        }
        return false;
    }
}
