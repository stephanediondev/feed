<?php
namespace Readerself\CoreBundle\Controller;

use Readerself\CoreBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends AbstractController
{
    public function indexAction(Request $request)
    {
        return $this->render('ReaderselfCoreBundle:Setting:index.html.twig', []);
    }
}
