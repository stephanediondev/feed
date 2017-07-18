<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Readerself\CoreBundle\Controller\AbstractController;

class ProxyController extends AbstractController
{
    public function indexAction(Request $request, $token)
    {
        $file = base64_decode(urldecode($token));

        $opts = [
            'http' => [
                'method' => 'GET',
                'user_agent'=> $request->server->get('HTTP_USER_AGENT'),
            ],
        ];

        $context = stream_context_create($opts);

        if($file != '' && (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://')) {
            $response = new StreamedResponse();

            $imginfo = @getimagesize($file);
            if($imginfo) {
                $response->headers->set('Content-Type', $imginfo['mime']);
            }

            $response->setCallback(function () use ($file, $context) {
                @readfile($file, false, $context);
            });

            return $response;
        }
    }
}
