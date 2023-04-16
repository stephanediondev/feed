<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Manager\ConnectionManager;
use App\Model\QueryParameterPageModel;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractAppController extends AbstractController
{
    protected ?Request $request;

    protected PaginatorInterface $paginator;

    protected ConnectionManager $connectionManager;

    /**
     * @required
     */
    public function setRequired(RequestStack $requestStack, PaginatorInterface $paginator, ConnectionManager $connectionManager): void
    {
        date_default_timezone_set('UTC');

        $this->request = $requestStack->getMainRequest();
        $this->paginator = $paginator;
        $this->connectionManager = $connectionManager;
    }

    protected function getMember(): ?Member
    {
        if (true === $this->getUser() instanceof Member) {
            return $this->getUser();
        }

        return null;
    }

    /**
     * @param array<mixed> $data
     */
    protected function jsonResponse(array $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $data['meta']['datetime'] = (new \Datetime())->format('Y-m-d H:i:s');
        $data['meta']['timezone'] = date_default_timezone_get();

        $response = new JsonResponse($data, $status);
        return $response;
    }

    /**
     * @return array<mixed>
     */
    protected function getFormErrors(FormInterface $form): array
    {
        $data = [
            'errors' => [],
        ];
        foreach ($form->getErrors(true) as $error) {
            if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                if ('data' == $error->getOrigin()->getName()) {
                    $data['errors'][] = [
                        'status' => '400',
                        'source' => [
                            'pointer' => '/data',
                        ],
                        'title' => $error->getMessage(),
                    ];
                } else {
                    $data['errors'][] = [
                        'status' => '400',
                        'source' => [
                            'pointer' => '/data/attributes/'.$error->getOrigin()->getName(),
                        ],
                        'title' => 'Invalid attribute',
                        'detail' => $error->getMessage(),
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @return ?array<mixed>
     */
    protected function getContent(Request $request): ?array
    {
        if ($request->headers->get('Content-Type')) {
            if (stristr($request->headers->get('Content-Type'), 'multipart/form-data') || stristr($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
                return $request->request->all();
            } elseif (stristr($request->headers->get('Content-Type'), 'application/json') && $content = $request->getContent()) {
                return json_decode($content, true);
            }
        }

        return null;
    }

    /**
     * @return PaginationInterface<mixed>
     */
    public function paginateAbstract(?QueryBuilder $queryBuilder): PaginationInterface
    {
        $pageNumber = 1;
        $pageSize = 20;

        if ($this->request) {
            $page = new QueryParameterPageModel($this->request->query->all('page'));

            if ($page->getNumber() && $page->getSize()) {
                $pageNumber = $page->getNumber();
                $pageSize = $page->getSize();
            }
        }

        return $this->paginator->paginate($queryBuilder, $pageNumber, $pageSize, [
            'pageParameterName' => 'page_',
            'sortFieldParameterName' => 'sort_field_',
            'sortDirectionParameterName' => 'sort_direction_',
        ]);
    }
}
