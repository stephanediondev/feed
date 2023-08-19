<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProxyController extends AbstractAppController
{
    #[Route(path: '/proxy', name: 'proxy', methods: ['GET'], priority: 25)]
    public function index(Request $request, #[MapQueryParameter] ?string $token): ?Response
    {
        $response = new Response();

        if ($token) {
            $file = base64_decode(urldecode(strval($token)));

            if ($file != '' && (str_starts_with($file, 'http://') || str_starts_with($file, 'https://'))) {
                try {
                    $opts = [
                        'http' => [
                            'method' => 'GET',
                            'user_agent'=> $request->headers->get('User-Agent'),
                        ]
                    ];

                    $context = stream_context_create($opts);

                    if ($content = file_get_contents($file, false, $context)) {
                        $contentType = (new \finfo(FILEINFO_MIME))->buffer($content);

                        $response->setContent($content);
                        $response->setStatusCode(Response::HTTP_OK);
                        if ($contentType) {
                            $response->headers->set('Content-Type', $contentType);
                        }

                        return $response;
                    }
                } catch (\Exception $e) {
                    throw new NotFoundHttpException($e->getMessage());
                }
            }
        }

        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        return $response;
    }
}
