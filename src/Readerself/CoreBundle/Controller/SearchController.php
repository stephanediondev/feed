<?php
namespace Readerself\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Readerself\CoreBundle\Controller\AbstractController;

use Readerself\CoreBundle\Manager\ItemManager;
use Readerself\CoreBundle\Manager\CategoryManager;
use Readerself\CoreBundle\Manager\ActionManager;
use Readerself\CoreBundle\Manager\SearchManager;

class SearchController extends AbstractController
{
    protected $itemManager;

    protected $categoryManager;

    protected $actionManager;

    protected $searchManager;

    public function __construct(
        ItemManager $itemManager,
        CategoryManager $categoryManager,
        ActionManager $actionManager,
        SearchManager $searchManager
    ) {
        $this->itemManager = $itemManager;
        $this->categoryManager = $categoryManager;
        $this->actionManager = $actionManager;
        $this->searchManager = $searchManager;
    }

    /**
     * Search feeds.
     *
     * @ApiDoc(
     *     section="Search",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="q", "dataType"="string", "required"=true, "description"="query"},
     *         {"name"="sort_field", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="sort_direction", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""20""", "description"="items per page"},
     *     },
     * )
     */
    public function feedsAction(Request $request)
    {
        return $this->getResults($request, 'feed');
    }

    /**
     * Search items.
     *
     * @ApiDoc(
     *     section="Search",
     *     headers={
     *         {"name"="X-CONNECTION-TOKEN","required"=true},
     *     },
     *     parameters={
     *         {"name"="q", "dataType"="string", "required"=true, "description"="query"},
     *         {"name"="sort_field", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="sort_direction", "dataType"="string", "required"=false, "format"="""asc"" or ""desc"", default ""desc""", "description"=""},
     *         {"name"="page", "dataType"="integer", "required"=false, "format"="default ""1""", "description"="page number"},
     *         {"name"="perPage", "dataType"="integer", "required"=false, "format"="default ""20""", "description"="items per page"},
     *     },
     * )
     */
    public function itemsAction(Request $request)
    {
        return $this->getResults($request, 'item');
    }

    private function getResults(Request $request, $type)
    {
        $data = [];
        if(!$member = $this->validateToken($request)) {
            return new JsonResponse($data, 403);
        }

        $sortFields = ['date.sort', '_score', 'title.sort'];
        $sortDirection = ['asc', 'desc'];

        if($request->query->get('q')) {
            if(!array_key_exists($request->query->get('sort_field'), $sortFields)) {
                $sortField = '_score';
            } else {
                $sortField = $request->query->get('sort_field');
            }
            if(!array_key_exists($request->query->get('sort_direction'), $sortDirection)) {
                $sortDirection = 'desc';
            } else {
                $sortDirection = $request->query->get('sort_direction');
            }

            $size = 20;
            $from = $request->query->get('from', 0);
            $path = '/'.$this->searchManager->getIndex().'/_search?size='.intval($size).'&type='.$type.'&from='.intval($from);

            $body = array();
            $body['sort'] = array(
                $sortField => array(
                    'order' => $sortDirection,
                    ),
            );

            if($type == 'feed') {
                $body['query'] = array(
                    'query_string' => array(
                        'fields' => ['title', 'description', 'website'],
                        'query' => $request->query->get('q'),
                    ),
                );
                $body['highlight'] = array(
                    //'encoder' => 'html',
                    'pre_tags' => array('<strong>'),
                    'post_tags' => array('</strong>'),
                    'fields' => array(
                        'title' => array(
                            'fragment_size' => 255,
                            'number_of_fragments' => 1,
                        ),
                        'description' => array(
                            'fragment_size' => 255,
                            'number_of_fragments' => 1,
                        ),
                    ),
                );
            }

            if($type == 'item') {
                $body['query'] = array(
                    'query_string' => array(
                        'fields' => ['title', 'content', 'feed.title', 'author.title'],
                        'query' => $request->query->get('q'),
                    ),
                );
                $body['highlight'] = array(
                    //'encoder' => 'html',
                    'pre_tags' => array('<strong>'),
                    'post_tags' => array('</strong>'),
                    'fields' => array(
                        'title' => array(
                            'fragment_size' => 255,
                            'number_of_fragments' => 1,
                        ),
                        'content' => array(
                            'fragment_size' => 255,
                            'number_of_fragments' => 1,
                        ),
                    ),
                );
            }

            /*if(!$parameters->get('page')->getAttribute('all_languages')) {
                $body['filter'] = array(
                    'term' => array(
                        'language.code' => $parameters->get('page')->getLanguage()->getCode(),
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

            $data['entries'] = [];

            if(isset($result->error) == 0) {
                if($type == 'item') {
                    $shareEntries = $this->itemManager->shareManager->getList();
                }

                $index = 0;
                foreach($result['hits']['hits'] as $hit) {
                    if($type == 'feed') {
                        $feed = $this->get('readerself_core_manager_feed')->getOne(['id' => $hit['_id']]);
                        $subscription = $this->get('readerself_core_manager_feed')->subscriptionManager->getOne(['member' => $member, 'feed' => $feed]);

                        $data['entries'][$index] = $feed->toArray();
                        if($subscription) {
                            $data['entries'][$index]['subscription'] = $subscription->toArray();
                        }

                        if(isset($hit['highlight']['title']) == 1) {
                            $data['entries'][$index]['title'] = $hit['highlight']['title'][0];
                        }
                        if(isset($hit['highlight']['description']) == 1) {
                            try {
                                $options = [
                                    'output-xhtml' => true,
                                    'clean' => true,
                                    'wrap-php' => true,
                                    'doctype' => 'omit',
                                    'show-body-only' => true,
                                    'drop-proprietary-attributes' => true,
                                ];
                                $tidy = new \tidy();
                                $tidy->parseString($hit['highlight']['description'][0], $options, 'utf8');
                                $tidy->cleanRepair();
                                $data['entries'][$index]['description'] = $tidy->value;
                            } catch (Exception $e) {
                            }
                        }
                    }

                    if($type == 'item') {
                        $item = $this->itemManager->getOne(['id' => $hit['_id']]);

                        $actions = [];
                        foreach($this->actionManager->actionItemMemberManager->getList(['member' => $member, 'item' => $item]) as $action) {
                            $actions[] = $action->toArray();
                        }

                        $socials = [];
                        foreach($shareEntries as $shareEntry) {
                            $link = $shareEntry->getLink();
                            $link = str_replace('{title}', urlencode($item->getTitle()), $link);
                            $link = str_replace('{link}', urlencode($item->getLink()), $link);
                            $socials[] = ['id' => $shareEntry->getId(), 'title' => $shareEntry->getTitle(), 'link' => $link];
                        }

                        $categories = [];
                        foreach($this->categoryManager->itemCategoryManager->getList(['member' => $member, 'item' => $item]) as $itemCategory) {
                            $categories[] = $itemCategory->toArray();
                        }

                        $data['entries'][$index] = $item->toArray();
                        $data['entries'][$index]['actions'] = $actions;
                        $data['entries'][$index]['categories'] = $categories;
                        $data['entries'][$index]['enclosures'] = [];
                        $data['entries'][$index]['socials'] = $socials;

                        if(isset($hit['highlight']['title']) == 1) {
                            $data['entries'][$index]['title'] = $hit['highlight']['title'][0];
                        }
                        if(isset($hit['highlight']['content']) == 1) {
                            try {
                                $options = [
                                    'output-xhtml' => true,
                                    'clean' => true,
                                    'wrap-php' => true,
                                    'doctype' => 'omit',
                                    'show-body-only' => true,
                                    'drop-proprietary-attributes' => true,
                                ];
                                $tidy = new \tidy();
                                $tidy->parseString($hit['highlight']['content'][0], $options, 'utf8');
                                $tidy->cleanRepair();
                                $data['entries'][$index]['content'] = $tidy->value;
                            } catch (Exception $e) {
                            }
                        }
                    }

                    $index++;
                }

                $data['entries_entity'] = $type;
                $data['entries_total'] = $result['hits']['total'];
                //$data['entries_pages'] = $pages = $pagination->getPageCount();

                $pagination = [];
                if($result['hits']['total'] > $size) {
                    $total = $result['hits']['total'] - 1;
                    $start = 1;
                    for($i=0;$i<=$total;$i = $i + $size) {
                        $pagination[$start] = $i;
                        $start++;
                    }
                    $data['current_from'] = intval($from);
                }
                $data['pagination'] =  $pagination;
            }
        }

        return new JsonResponse($data);
    }
}
