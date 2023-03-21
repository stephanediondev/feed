<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_')]
class ProxyController extends AbstractAppController
{
    #[Route(path: '/proxy', name: 'proxy', methods: ['GET'])]
    public function index(Request $request)
    {
        $file = base64_decode(urldecode($request->query->get('token')));

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'user_agent'=> $_SERVER['HTTP_USER_AGENT']
            )
        );

        $context = stream_context_create($opts);

        if ($file != '' && (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://')) {
            $imginfo = @getimagesize($file);
            if ($imginfo) {
                header('Content-type: '.$imginfo['mime']);
            }
            @readfile($file, false, $context);
        }
        exit(0);
    }
}
