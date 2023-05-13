<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Manager\ConnectionManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractAppController extends AbstractController
{
    protected PaginatorInterface $paginator;

    protected ConnectionManager $connectionManager;

    /**
     * @required
     */
    public function setRequired(PaginatorInterface $paginator, ConnectionManager $connectionManager): void
    {
        date_default_timezone_set('UTC');

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
        $data['jsonapi']['version'] = '1.1';
        $data['meta']['datetime'] = (new \Datetime())->format('Y-m-d H:i:s');
        $data['meta']['timezone'] = date_default_timezone_get();

        switch($status) {
            case 404:
                $data['errors'] = [['status' => '404', 'title' => 'Not Found']];
                break;
        }

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
    protected function paginateAbstract(Request $request, ?QueryBuilder $queryBuilder): PaginationInterface
    {
        $pageNumber = 1;
        $pageSize = 20;

        $pageModel = new QueryParameterPageModel($request->query->all('page'));

        if ($pageModel->getNumber() && $pageModel->getSize()) {
            $pageNumber = $pageModel->getNumber();
            $pageSize = $pageModel->getSize();
        }

        return $this->paginator->paginate($queryBuilder, $pageNumber, $pageSize, [
            'pageParameterName' => 'page_',
            'sortFieldParameterName' => 'sort_field_',
            'sortDirectionParameterName' => 'sort_direction_',
        ]);
    }

    /**
     * @param PaginationInterface<mixed> $pagination
     * @return array<mixed>
     */
    protected function jsonApi(Request $request, PaginationInterface $pagination, ?QueryParameterSortModel $sortModel, ?QueryParameterFilterModel $filtersModel): array
    {
        $data = [];
        $pages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $pagePrevious = $pagination->getCurrentPageNumber() - 1;
        if ($pagePrevious >= 1) {
            $data['meta']['page_previous'] = $pagePrevious;
        }
        $pageNext = $pagination->getCurrentPageNumber() + 1;
        if ($pageNext <= $pages) {
            $data['meta']['page_next'] = $pageNext;
        }

        $data['meta']['results'] = $pagination->getTotalItemCount();
        $data['meta']['pages'] = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $data['meta']['page_size'] = $pagination->getItemNumberPerPage();
        $data['meta']['page_number'] = $pagination->getCurrentPageNumber();

        $filters = [];
        if ($sortModel) {
            if ($sortRaw = $sortModel->raw()) {
                $filters['sort'] = $sortRaw;
            }
        }
        if ($filtersModel) {
            foreach ($filtersModel->toArray() as $key => $value) {
                $filters['filter['.$key.']'] = $value;
            }
        }

        if (0 < $pagination->getTotalItemCount()) {
            $data['links']['first'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => 1]), UrlGeneratorInterface::ABSOLUTE_URL);
            $data['links']['last'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $data['meta']['pages']]), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (1 < $pagination->getCurrentPageNumber()) {
            $previous = $pagination->getCurrentPageNumber() - 1;
            $data['links']['prev'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $previous]), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($data['meta']['pages'] > $pagination->getCurrentPageNumber()) {
            $next = $pagination->getCurrentPageNumber() + 1;
            $data['links']['next'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $next]), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $data;
    }
}
