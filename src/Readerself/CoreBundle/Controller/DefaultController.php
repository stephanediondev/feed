<?php
namespace Readerself\CoreBundle\Controller;

use Readerself\CoreBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Minishlink\WebPush\WebPush;

class DefaultController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $this->get('readerself_core_manager_collection')->start();

        return $this->render('AxipiFeedBundle::default.html.twig', []);
    }
}
