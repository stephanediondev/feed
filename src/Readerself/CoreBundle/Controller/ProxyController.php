<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Readerself\CoreBundle\Controller\AbstractController;

class ProxyController extends AbstractController
{
    public function indexAction(Request $request, $token)
    {
        $file = base64_decode(urldecode($token));

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'user_agent'=> $_SERVER['HTTP_USER_AGENT']
            )
        );

        $context = stream_context_create($opts);

        if($file != '' && (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://')) {
            $imginfo = @getimagesize($file);
            if($imginfo) {
                header('Content-type: '.$imginfo['mime']);
            }
            @readfile($file, false, $context);
        }
        exit(0);
    }
}
