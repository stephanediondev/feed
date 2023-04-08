<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/', name: 'app_')]
class ManifestController extends AbstractController
{
    #[Route(path: 'manifest.webmanifest', name: 'manifest_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        return $this->render('manifest.json.twig', [], $response);
    }
}
