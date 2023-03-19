<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Controller\AbstractAppController;

class ContentController extends AbstractAppController
{
    public function index(Request $request)
    {
        $data = [];
        return $this->render('ReaderselfCoreBundle::base.html.twig', $data);
    }
}
