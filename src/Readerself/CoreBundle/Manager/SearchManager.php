<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use PDO;

class SearchManager extends AbstractManager
{
    protected $enabled;

    protected $index;

    protected $url;

    public function setElasticsearch($enabled, $index, $url)
    {
        $this->enabled = $enabled;
        $this->index = $index;
        $this->url = $url;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function start()
    {
        if($this->getEnabled()) {
            $this->init();

            //feeds
            $sql = 'SELECT fed.* FROM feed AS fed';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'PUT';

                $path = '/'.$this->getIndex().'/feed/'.$result['id'];

                $body = array(
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'website' => $result['website'],
                    'language' => $result['language'],
                );
                $this->query($action, $path, $body);

                /*$insertActionItem = [
                    'item_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_item', $insertActionItem);*/
            }

            //categories
            $sql = 'SELECT cat.*
            FROM category AS cat
            WHERE cat.id NOT IN (SELECT act_cat.category_id FROM action_category AS act_cat WHERE act_cat.category_id = cat.id AND act_cat.action_id = 11)
            LIMIT 0,2000';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'PUT';

                $path = '/'.$this->getIndex().'/category/'.$result['id'];

                $body = array(
                    'title' => $result['title'],
                );
                $this->query($action, $path, $body);

                $insertActionCategory = [
                    'category_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_category', $insertActionCategory);
            }

            //items
            $sql = 'SELECT itm.*, auh.title AS author_title, fed.title AS feed_title, fed.language AS feed_language
            FROM item AS itm
            LEFT JOIN author AS auh ON auh.id = itm.author_id
            LEFT JOIN feed AS fed ON fed.id = itm.feed_id
            WHERE itm.content IS NOT NULL AND itm.id NOT IN (SELECT act_itm.item_id FROM action_item AS act_itm WHERE act_itm.item_id = itm.id AND act_itm.action_id = 11)
            LIMIT 0,1000';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'PUT';

                $path = '/'.$this->getIndex().'/item/'.$result['id'];

                $body = array(
                    'feed' => array(
                        'id' => $result['feed_id'],
                        'title' => $result['feed_title'],
                        'language' => $result['feed_language'],
                    ),
                    'title' => $result['title'],
                    'date' => $result['date'],
                    'content' => $result['content'],
                );
                if($result['author_id']) {
                    $body['author'] = array(
                        'id' => $result['author_id'],
                        'title' => $result['author_title'],
                    );
                }
                $this->query($action, $path, $body);

                $insertActionItem = [
                    'item_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_item', $insertActionItem);
            }
        }
    }

    public function query($action, $path, $body = false)
    {
        if($this->getEnabled()) {
            $path = $this->getUrl().$path;

            $ci = curl_init();
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $action);
            curl_setopt($ci, CURLOPT_URL, $path);
            if($body) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, json_encode($body));
            }
            $result = json_decode(curl_exec($ci), true);
            if($action == 'HEAD') {
                $result = curl_getinfo($ci, CURLINFO_HTTP_CODE);
            }
            return $result;
        }
    }

    private function init()
    {
        if($this->getEnabled()) {
            $path = '/'.$this->getIndex();
            $result = $this->query('HEAD', $path);

            if($result == 404) {
                $path = '/'.$this->getIndex();
                $result = $this->query('PUT', $path);
            }

            $path = '/'.$this->getIndex().'/_close';
            $result = $this->query('POST', $path);

            $path = '/'.$this->getIndex().'/_settings';
            $body = array(
                'settings' => array(
                    'index' => array(
                        'analysis' => array(
                            'analyzer' => array(
                                'case_insensitive_sort' => array(
                                    'filter' => array(
                                        'lowercase',
                                        'asciifolding',
                                    ),
                                    'tokenizer' => 'keyword',
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'/_open';
            $result = $this->query('POST', $path);

            $path = '/'.$this->getIndex().'/_mapping/feed';
            $body = array(
                'feed' => array(
                    'properties' => array( 
                        'title' => array( 
                            'type' => 'string',
                            'fields' => array(
                                'sort' => array( 
                                    'type' => 'string',
                                    'analyzer' => 'case_insensitive_sort',
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'/_mapping/category';
            $body = array(
                'category' => array(
                    'properties' => array( 
                        'title' => array( 
                            'type' => 'string',
                            'fields' => array(
                                'sort' => array( 
                                    'type' => 'string',
                                    'analyzer' => 'case_insensitive_sort',
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'/_mapping/item';
            $body = array(
                'item' => array(
                    'properties' => array( 
                        'title' => array( 
                            'type' => 'string',
                            'fields' => array(
                                'sort' => array( 
                                    'type' => 'string',
                                    'analyzer' => 'case_insensitive_sort',
                                ),
                            ),
                        ),
                        'date' => array( 
                            'type' => 'string',
                            'fields' => array(
                                'sort' => array( 
                                    'type' => 'string',
                                    'analyzer' => 'case_insensitive_sort',
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $result = $this->query('PUT', $path, $body);
        }
    }
}
