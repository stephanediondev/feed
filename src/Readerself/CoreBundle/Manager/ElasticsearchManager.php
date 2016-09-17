<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use PDO;

class ElasticsearchManager extends AbstractManager
{
    protected $elasticsearchEnabled;

    protected $elasticsearchIndex;

    protected $elasticsearchUrl;

    public function setElasticsearch($enabled, $index, $url)
    {
        $this->elasticsearchEnabled = $enabled;
        $this->elasticsearchIndex = $index;
        $this->elasticsearchUrl = $url;
    }

    public function start()
    {
        if($this->elasticsearchEnabled) {
            $this->init();

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

                $path = '/'.$this->elasticsearchIndex.'/item/'.$result['id'];

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

    private function query($action, $path, $body = false)
    {
        if($this->elasticsearchEnabled) {
            $path = $this->elasticsearchUrl.$path;

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
        if($this->elasticsearchEnabled) {
            $path = '/'.$this->elasticsearchIndex;
            $result = $this->query('HEAD', $path);

            if($result == 404) {
                $path = '/'.$this->elasticsearchIndex;
                $result = $this->query('PUT', $path);
            }

            $path = '/'.$this->elasticsearchIndex.'/_close';
            $result = $this->query('POST', $path);

            $path = '/'.$this->elasticsearchIndex.'/_settings';
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

            $path = '/'.$this->elasticsearchIndex.'/_open';
            $result = $this->query('POST', $path);

            $path = '/'.$this->elasticsearchIndex.'/_mapping/item';
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
