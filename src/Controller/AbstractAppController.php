<?php

namespace App\Controller;

use App\Manager\MemberManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

abstract class AbstractAppController extends AbstractController
{
    protected ?Request $request;

    protected PaginatorInterface $paginator;

    protected TranslatorInterface $translator;

    protected MemberManager $memberManager;

    /**
     * @required
     */
    public function setRequired(RequestStack $requestStack, PaginatorInterface $paginator, TranslatorInterface $translator, MemberManager $memberManager): void
    {
        date_default_timezone_set('UTC');

        $this->request = $requestStack->getMainRequest();
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->memberManager = $memberManager;
    }

    public function validateToken(Request $request, $type = 'login')
    {
        if ($request->headers->get('X-CONNECTION-TOKEN') && $connection = $this->memberManager->connectionManager->getOne(['type' => $type, 'token' => $request->headers->get('X-CONNECTION-TOKEN')])) {
            return $connection->getMember();
        }
        return false;
    }

    public function paginateAbstract(?QueryBuilder $queryBuilder, int $page, ?int $limit = 20): ?PaginationInterface
    {
        /*if ($this->request) {
            $page = intval($this->request->query->get('page_'.$parameterName));
        }*/

        if (0 === $page) {
            $page = 1;
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, [
        ]);
    }

    public function renderAbstract(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->headers->set('X-Frame-Options', 'sameorigin');

        return $this->render($view, $parameters, $response);
    }
}
