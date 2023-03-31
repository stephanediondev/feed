<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_')]
class ProxyController extends AbstractAppController
{
    #[Route(path: '/proxy', name: 'proxy', methods: ['GET'])]
    public function index(Request $request): ?Response
    {
        if ($token = $request->query->get('token')) {
            $file = base64_decode(urldecode(strval($token)));

            if ($file != '' && (str_starts_with($file, 'http://') || str_starts_with($file, 'https://'))) {
                $opts = [
                    'http' => [
                        'method' => 'GET',
                        'user_agent'=> $_SERVER['HTTP_USER_AGENT']
                    ]
                ];

                $context = stream_context_create($opts);

                if ($content = file_get_contents($file, false, $context)) {
                    $contentType = (new \finfo(FILEINFO_MIME))->buffer($content);

                    $response = new Response();
                    $response->setContent($content);
                    if ($contentType) {
                        $response->headers->set('Content-Type', $contentType);
                    }

                    return $response;
                }
            }
        }

        return null;
    }
}
