<?php

namespace App\Controller;

use App\Manager\SearchManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\AbstractAppController;

#[Route(path: '/api', name: 'api_status_')]
class StatusController extends AbstractAppController
{
    private SearchManager $searchManager;

    public function __construct(SearchManager $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    #[Route('/status', name: 'index', methods: ['GET'])]
    public function index(Request $request)
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        if (!$memberConnected->getAdministrator()) {
            return new JsonResponse($data, 403);
        }

        $types = ['author', 'category', 'feed', 'item'];
        foreach ($types as $type) {
            $data['types'][$type] = [];
        }

        foreach ($types as $type) {
            $data['types'][$type]['database'] = $this->searchManager->count($type);
        }

        $data['_server'] = $_SERVER;

        $data['function'] = [];
        $data['function']['phpversion'] = phpversion();
        $data['function']['php_sapi_name'] = php_sapi_name();
        $data['function']['get_current_user'] = get_current_user();

        $data['ini_get'] = [];
        $data['ini_get']['file_uploads'] = ini_get('file_uploads');
        $data['ini_get']['upload_max_filesize'] = ini_get('upload_max_filesize');
        $data['ini_get']['post_max_size'] = ini_get('post_max_size');
        $data['ini_get']['memory_limit'] = ini_get('memory_limit');

        $data['extension'] = [];
        $data['extension']['gmp'] = extension_loaded('gmp');
        $data['extension']['mbstring'] = extension_loaded('mbstring');
        $data['extension']['iconv'] = extension_loaded('iconv');
        $data['extension']['apcu'] = extension_loaded('apcu');
        $data['extension']['tidy'] = extension_loaded('tidy');
        $data['extension']['dom'] = extension_loaded('dom');

        $data['symfony'] = \Symfony\Component\HttpKernel\Kernel::VERSION;

        if ($this->searchManager->getEnabled()) {
            $data['search']['enabled'] = true;

            foreach ($types as $type) {
                $path = '/'.$this->searchManager->getIndex().'_'.$type.'/_stats';
                $result = $this->searchManager->query('GET', $path);
                if (isset($result->error) == 0) {
                    $data['types'][$type]['search'] = $result;
                    $data['types'][$type]['index'] = $this->searchManager->getIndex();
                }
            }

            $path = '/_cluster/health';
            $result = $this->searchManager->query('GET', $path);
            if (isset($result->error) == 0) {
                $data['search']['health'] = $result;
            }

            $path = '/_nodes';
            $result = $this->searchManager->query('GET', $path);
            if (isset($result->error) == 0) {
                $data['search']['nodes'] = $result;
            }
        }

        return new JsonResponse($data);
    }
}
