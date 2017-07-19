<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Readerself\CoreBundle\Controller\AbstractController;

class ContentController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $data = [];
        return $this->render('ReaderselfCoreBundle::index.html.twig', $data);
    }
}
