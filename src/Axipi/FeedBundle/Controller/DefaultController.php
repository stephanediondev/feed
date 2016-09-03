<?php
namespace Axipi\FeedBundle\Controller;

use Axipi\FeedBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Minishlink\WebPush\WebPush;

class DefaultController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $this->get('axipi_core_manager_feed')->updateAll();

        return $this->render('AxipiFeedBundle::default.html.twig', []);
    }
}
