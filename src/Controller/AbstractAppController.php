<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Helper\JwtHelper;
use App\Manager\ConnectionManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAppController extends AbstractController
{
    protected ?Request $request;

    protected PaginatorInterface $paginator;

    protected TranslatorInterface $translator;

    protected ConnectionManager $connectionManager;

    /**
     * @required
     */
    public function setRequired(RequestStack $requestStack, PaginatorInterface $paginator, TranslatorInterface $translator, ConnectionManager $connectionManager): void
    {
        date_default_timezone_set('UTC');

        $this->request = $requestStack->getMainRequest();
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->connectionManager = $connectionManager;
    }

    /**
     * @return ?array<mixed>
     */
    protected function getContent(Request $request): ?array
    {
        if (stristr($request->headers->get('Content-Type'), 'multipart/form-data') || stristr($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
            return $request->request->all();
        } elseif (stristr($request->headers->get('Content-Type'), 'application/json') && $content = $request->getContent()) {
            return json_decode($content, true);
        }

        return null;
    }

    /**
     * @return PaginationInterface<mixed>
     */
    public function paginateAbstract(?QueryBuilder $queryBuilder, int $page, ?int $limit = 20): PaginationInterface
    {
        /*if ($this->request) {
            $page = intval($this->request->query->get('page_'.$parameterName));
        }*/

        if (0 === $page) {
            $page = 1;
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, []);
    }
}
