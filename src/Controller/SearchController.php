<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Helper\CleanHelper;
use App\Manager\ActionAuthorManager;
use App\Manager\ActionCategoryManager;
use App\Manager\ActionFeedManager;
use App\Manager\ActionItemManager;
use App\Manager\AuthorManager;
use App\Manager\CategoryManager;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use App\Manager\SearchManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/api', name: 'api_search_', priority: 20)]
class SearchController extends AbstractAppController
{
    private ActionFeedManager $actionFeedManager;
    private ActionAuthorManager $actionAuthorManager;
    private ActionCategoryManager $actionCategoryManager;
    private ActionItemManager $actionItemManager;
    private AuthorManager $authorManager;
    private FeedManager $feedManager;
    private ItemManager $itemManager;
    private SearchManager $searchManager;
    private CategoryManager $categoryManager;

    public function __construct(ActionFeedManager $actionFeedManager, ActionAuthorManager $actionAuthorManager, ActionCategoryManager $actionCategoryManager, ActionItemManager $actionItemManager, AuthorManager $authorManager, FeedManager $feedManager, ItemManager $itemManager, SearchManager $searchManager, CategoryManager $categoryManager)
    {
        $this->actionFeedManager = $actionFeedManager;
        $this->actionAuthorManager = $actionAuthorManager;
        $this->actionCategoryManager = $actionCategoryManager;
        $this->actionItemManager = $actionItemManager;
        $this->authorManager = $authorManager;
        $this->feedManager = $feedManager;
        $this->itemManager = $itemManager;
        $this->searchManager = $searchManager;
        $this->categoryManager = $categoryManager;
    }

    #[Route(path: '/feeds/search', name: 'feeds', methods: ['GET'])]
    public function feeds(Request $request): JsonResponse
    {
        return $this->getResults($request, 'feed');
    }

    #[Route(path: '/categories/search', name: 'categories', methods: ['GET'])]
    public function categories(Request $request): JsonResponse
    {
        return $this->getResults($request, 'category');
    }

    #[Route(path: '/authors/search', name: 'authors', methods: ['GET'])]
    public function authors(Request $request): JsonResponse
    {
        return $this->getResults($request, 'author');
    }

    #[Route(path: '/items/search', name: 'items', methods: ['GET'])]
    public function items(Request $request): JsonResponse
    {
        return $this->getResults($request, 'item');
    }

    private function getResults(Request $request, string $type): JsonResponse
    {
        $data = [];

        $filtersModel = new QueryParameterFilterModel($request->query->all('filter'));

        if ($filtersModel->get('query')) {
            $pageModel = new QueryParameterPageModel($request->query->all('page'));

            $sortModel = new QueryParameterSortModel($request->query->get('sort'));

            if ($sortGet = $sortModel->get()) {
                $sortDirection = $sortGet['direction'];
                $sortField = strtolower($sortGet['field']);
            } else {
                $sortDirection = 'desc';
                $sortField = '_score';
            }

            $from = ($pageModel->getSize() * $pageModel->getNumber()) - $pageModel->getSize();
            $path = '/'.$this->searchManager->getIndex().'_'.$type.'/_search?size='.$pageModel->getSize().'&from='.intval($from);

            $body = [];
            $body['sort'] = [
                $sortField => [
                    'order' => $sortDirection,
                ],
            ];

            $fields = null;

            switch ($type) {
                case 'feed':
                    $fields = ['title', 'description', 'website'];
                    break;
                case 'category':
                    $fields = ['title'];
                    break;
                case 'author':
                    $fields = ['title'];
                    break;
                case 'item':
                    $fields = ['title', 'content', 'feed.title', 'author.title'];
                    break;
            }

            if ($fields) {
                $body['query'] = [
                    'query_string' => [
                        'fields' => $fields,
                        'query' => $filtersModel->get('query'),
                    ],
                ];
            }

            $result = $this->searchManager->query('GET', $path, $body);

            if (true === isset($result['hits']['hits'])) {
                $data['entries_entity'] = $type;
                if (true == isset($result['hits']['total']['value'])) {
                    $data['meta']['results'] = $result['hits']['total']['value'];
                } else {
                    $data['meta']['results'] = $result['hits']['total'];
                }
                $data['meta']['pages'] = $pages = ceil($data['meta']['results'] / $pageModel->getSize());
                $data['meta']['page_number'] = $pageModel->getNumber();
                $pagePrevious = $pageModel->getNumber() - 1;
                if ($pagePrevious >= 1) {
                    $data['meta']['page_previous'] = $pagePrevious;
                }
                $pageNext = $pageModel->getNumber() + 1;
                if ($pageNext <= $pages) {
                    $data['meta']['page_next'] = $pageNext;
                }

                $data['meta']['page_size'] = $pageModel->getSize();

                $filters = [];
                if ($request->query->get('sort')) {
                    $filters['sort'] = $request->query->get('sort');
                }
                foreach ($filtersModel->toArray() as $key => $value) {
                    $filters['filter['.$key.']'] = $value;
                }

                if (0 < $data['meta']['results']) {
                    $data['links']['first'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => 1]), UrlGeneratorInterface::ABSOLUTE_URL);
                    $data['links']['last'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $data['meta']['pages']]), UrlGeneratorInterface::ABSOLUTE_URL);
                }

                if (1 < $data['meta']['page_number']) {
                    $previous = $data['meta']['page_number'] - 1;
                    $data['links']['prev'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $previous]), UrlGeneratorInterface::ABSOLUTE_URL);
                }

                if ($data['meta']['pages'] > $data['meta']['page_number']) {
                    $next = $data['meta']['page_number'] + 1;
                    $data['links']['next'] = $this->generateUrl($request->get('_route'), array_merge($filters, ['page[number]' => $next]), UrlGeneratorInterface::ABSOLUTE_URL);
                }

                $data['entries'] = [];

                foreach ($result['hits']['hits'] as $hit) {
                    switch ($type) {
                        case 'feed':
                            $feed = $this->feedManager->getOne(['id' => $hit['_id']]);
                            if ($feed) {
                                $actions = $this->actionFeedManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $this->getMember(), 'feed' => $feed])->getResult() as $feedCategory) {
                                    $categories[] = $feedCategory->toArray();
                                }

                                $entry = $feed->toArray();
                                $entry['score'] = $hit['_score'];
                                foreach ($actions as $action) {
                                    $entry[$action->getAction()->getTitle()] = true;
                                }
                                $entry['categories'] = $categories;

                                $data['entries'][] = $entry;
                            } else {
                                $action = 'DELETE';
                                $path = '/'.$this->searchManager->getIndex().'/feed/'.$hit['_id'];
                                $body = [];
                                $this->searchManager->query($action, $path, $body);
                            }
                            break;
                        case 'category':
                            $category = $this->categoryManager->getOne(['id' => $hit['_id']]);
                            if ($category) {
                                $actions = $this->actionCategoryManager->getList(['member' => $this->getMember(), 'category' => $category])->getResult();

                                $entry = $category->toArray();
                                $entry['score'] = $hit['_score'];
                                foreach ($actions as $action) {
                                    $entry[$action->getAction()->getTitle()] = true;
                                }

                                $data['entries'][] = $entry;
                            } else {
                                $action = 'DELETE';
                                $path = '/'.$this->searchManager->getIndex().'/category/'.$hit['_id'];
                                $body = [];
                                $this->searchManager->query($action, $path, $body);
                            }
                            break;
                        case 'author':
                            $author = $this->authorManager->getOne(['id' => $hit['_id']]);
                            if ($author) {
                                $actions = $this->actionAuthorManager->getList(['member' => $this->getMember(), 'author' => $author])->getResult();

                                $entry = $author->toArray();
                                $entry['score'] = $hit['_score'];
                                foreach ($actions as $action) {
                                    $entry[$action->getAction()->getTitle()] = true;
                                }

                                $data['entries'][] = $entry;
                            } else {
                                $action = 'DELETE';
                                $path = '/'.$this->searchManager->getIndex().'/author/'.$hit['_id'];
                                $body = [];
                                $this->searchManager->query($action, $path, $body);
                            }
                            break;
                        case 'item':
                            $item = $this->itemManager->getOne(['id' => $hit['_id']]);
                            if ($item) {
                                $actions = $this->actionItemManager->getList(['member' => $this->getMember(), 'item' => $item])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->itemCategoryManager->getList(['member' => $this->getMember(), 'item' => $item])->getResult() as $itemCategory) {
                                    $categories[] = $itemCategory->toArray();
                                }

                                $entry = $item->toArray();
                                $entry['score'] = $hit['_score'];
                                foreach ($actions as $action) {
                                    $entry[$action->getAction()->getTitle()] = true;
                                }
                                $entry['categories'] = $categories;
                                $entry['enclosures'] = $this->itemManager->prepareEnclosures($item, $request);

                                $entry['content'] = CleanHelper::cleanContent($item->getContent(), 'display');

                                $data['entries'][] = $entry;
                            } else {
                                $action = 'DELETE';
                                $path = '/'.$this->searchManager->getIndex().'/item/'.$hit['_id'];
                                $body = [];
                                $this->searchManager->query($action, $path, $body);
                            }
                            break;
                    }
                }
            }
        }

        return $this->jsonResponse($data);
    }
}
