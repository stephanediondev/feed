<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ActionAuthorManager;
use App\Manager\ActionCategoryManager;
use App\Manager\ActionFeedManager;
use App\Manager\ActionItemManager;
use App\Manager\AuthorManager;
use App\Manager\CategoryManager;
use App\Manager\FeedManager;
use App\Manager\ItemManager;
use App\Manager\SearchManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_search_')]
class SearchController extends AbstractAppController
{
    private const LIMIT = 20;

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
        if (!$memberConnected = $this->validateToken($request)) {
            //return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $sortFields = ['date.sort', '_score', 'title.sort'];
        $sortDirection = ['asc', 'desc'];

        if ($request->query->get('q')) {
            $page = $request->query->getInt('page', 1);

            if (!array_key_exists(strval($request->query->get('sortField')), $sortFields)) {
                $sortField = '_score';
            } else {
                $sortField = $request->query->get('sortField');
            }
            if (!array_key_exists(strval($request->query->get('sortDirection')), $sortDirection)) {
                $sortDirection = 'desc';
            } else {
                $sortDirection = $request->query->get('sortDirection');
            }

            $size = self::LIMIT;
            $from = ($size * $page) - self::LIMIT;
            $path = '/'.$this->searchManager->getIndex().'_'.$type.'/_search?size='.intval($size).'&from='.intval($from);

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
                        'query' => $request->query->get('q'),
                    ],
                ];
            }

            /*if(!$parameters->get('page')->getAttribute('all_languages')) {
                $body['filter'] = array(
                    'term' => array(
                        'feed.language' => 'en',
                    ),
                );
            }*/

            /*if($request->query->get('date_from') && $request->query->get('date_to')) {
                $body['filter'] = array(
                    'range' => array(
                        'date.sort' => array(
                            'gte' => $request->query->get('date_from'),
                            'lte' => $request->query->get('date_to'),
                            'format' => 'YYYY-MM-DD',
                        ),
                    ),
                );
            }*/

            $result = $this->searchManager->query('GET', $path, $body);

            if (isset($result['hits']['hits']) == 1) {
                $data['entries_entity'] = $type;
                if (true == isset($result['hits']['total']['value'])) {
                    $data['entries_total'] = $result['hits']['total']['value'];
                } else {
                    $data['entries_total'] = $result['hits']['total'];
                }
                $data['entries_pages'] = $pages = ceil($data['entries_total'] / self::LIMIT);
                $data['entries_page_current'] = $page;
                $pagePrevious = $page - 1;
                if ($pagePrevious >= 1) {
                    $data['entries_page_previous'] = $pagePrevious;
                }
                $pageNext = $page + 1;
                if ($pageNext <= $pages) {
                    $data['entries_page_next'] = $pageNext;
                }

                $data['entries'] = [];

                foreach ($result['hits']['hits'] as $hit) {
                    switch ($type) {
                        case 'feed':
                            $feed = $this->feedManager->getOne(['id' => $hit['_id']]);
                            if ($feed) {
                                $actions = $this->actionFeedManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->feedCategoryManager->getList(['member' => $memberConnected, 'feed' => $feed])->getResult() as $feedCategory) {
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
                                $actions = $this->actionCategoryManager->getList(['member' => $memberConnected, 'category' => $category])->getResult();

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
                                $actions = $this->actionAuthorManager->getList(['member' => $memberConnected, 'author' => $author])->getResult();

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
                                $actions = $this->actionItemManager->getList(['member' => $memberConnected, 'item' => $item])->getResult();

                                $categories = [];
                                foreach ($this->categoryManager->itemCategoryManager->getList(['member' => $memberConnected, 'item' => $item])->getResult() as $itemCategory) {
                                    $categories[] = $itemCategory->toArray();
                                }

                                $entry = $item->toArray();
                                $entry['score'] = $hit['_score'];
                                foreach ($actions as $action) {
                                    $entry[$action->getAction()->getTitle()] = true;
                                }
                                $entry['categories'] = $categories;
                                $entry['enclosures'] = $this->itemManager->prepareEnclosures($item, $request);

                                $entry['content'] = $this->itemManager->cleanContent($item->getContent(), 'display');

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

        return new JsonResponse($data);
    }
}
