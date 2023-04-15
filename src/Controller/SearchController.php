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

        $filters = new QueryParameterFilterModel($request->query->all('filter'));

        if ($filters->get('query')) {
            $page = new QueryParameterPageModel($request->query->all('page'));

            $sort = (new QueryParameterSortModel($request->query->get('sort')))->get();

            if ($sort) {
                $sortDirection = $sort['direction'];
                $sortField = strtolower($sort['field']);
            } else {
                $sortDirection = 'desc';
                $sortField = '_score';
            }

            $from = ($page->getSize() * $page->getNumber()) - $page->getSize();
            $path = '/'.$this->searchManager->getIndex().'_'.$type.'/_search?size='.$page->getSize().'&from='.intval($from);

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
                        'query' => $filters->get('query'),
                    ],
                ];
            }

            $result = $this->searchManager->query('GET', $path, $body);

            if (isset($result['hits']['hits']) == 1) {
                $data['entries_entity'] = $type;
                if (true == isset($result['hits']['total']['value'])) {
                    $data['entries_total'] = $result['hits']['total']['value'];
                } else {
                    $data['entries_total'] = $result['hits']['total'];
                }
                $data['entries_pages'] = $pages = ceil($data['entries_total'] / $page->getSize());
                $data['entries_page_current'] = $page->getNumber();
                $pagePrevious = $page->getNumber() - 1;
                if ($pagePrevious >= 1) {
                    $data['entries_page_previous'] = $pagePrevious;
                }
                $pageNext = $page->getNumber() + 1;
                if ($pageNext <= $pages) {
                    $data['entries_page_next'] = $pageNext;
                }

                $data['entries'] = [];

                foreach ($result['hits']['hits'] as $hit) {
                    switch ($type) {
                        case 'feed':
                            $feed = $this->feedManager->getOne(['id' => $hit['_id']]);
                            if ($feed) {
                                $actions = $this->actionFeedManager->getList(['member' => $this->getUser(), 'feed' => $feed])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $this->getUser(), 'feed' => $feed])->getResult() as $feedCategory) {
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
                                $actions = $this->actionCategoryManager->getList(['member' => $this->getUser(), 'category' => $category])->getResult();

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
                                $actions = $this->actionAuthorManager->getList(['member' => $this->getUser(), 'author' => $author])->getResult();

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
                                $actions = $this->actionItemManager->getList(['member' => $this->getUser(), 'item' => $item])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->itemCategoryManager->getList(['member' => $this->getUser(), 'item' => $item])->getResult() as $itemCategory) {
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
