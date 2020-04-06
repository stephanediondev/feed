<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use PDO;

class SearchManager extends AbstractManager
{
    protected $enabled;

    protected $index;

    protected $url;

    protected $username;

    protected $password;

    protected $sslVerifyPeer;

    public function setElasticsearch($enabled, $index, $url, $username, $password, $sslVerifyPeer)
    {
        $this->enabled = $enabled;
        $this->index = $index;
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->sslVerifyPeer = $sslVerifyPeer;
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

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSslVerifyPeer()
    {
        return $this->sslVerifyPeer;
    }

    public function start()
    {
        if($this->getEnabled()) {
            //$this->init();

            //feeds
            $sql = 'SELECT fed.* FROM feed AS fed';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'POST';

                $path = '/'.$this->getIndex().'_feed/doc/'.$result['id'];

                $body = [
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'website' => $result['website'],
                    'language' => $result['language'],
                    'date_created' => $result['date_created'],
                ];
                $this->query($action, $path, $body);
            }

            //categories
            $sql = 'SELECT cat.*
            FROM category AS cat
            WHERE cat.id NOT IN (SELECT act_cat.category_id FROM action_category AS act_cat WHERE act_cat.category_id = cat.id AND act_cat.action_id = 11)
            ORDER BY cat.id DESC LIMIT 0,3000';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'POST';

                $path = '/'.$this->getIndex().'_category/doc/'.$result['id'];

                $body = [
                    'title' => $result['title'],
                    'date_created' => $result['date_created'],
                ];
                $this->query($action, $path, $body);

                $insertActionCategory = [
                    'category_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_category', $insertActionCategory);
            }

            //authors
            $sql = 'SELECT aut.*
            FROM author AS aut
            WHERE aut.id NOT IN (SELECT act_aut.author_id FROM action_author AS act_aut WHERE act_aut.author_id = aut.id AND act_aut.action_id = 11)
            ORDER BY aut.id DESC LIMIT 0,3000';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'POST';

                $path = '/'.$this->getIndex().'_author/doc/'.$result['id'];

                $body = [
                    'title' => $result['title'],
                    'date_created' => $result['date_created'],
                ];
                $this->query($action, $path, $body);

                $insertActionCategory = [
                    'author_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_author', $insertActionCategory);
            }

            //items
            $sql = 'SELECT itm.*, auh.title AS author_title, fed.title AS feed_title, fed.language AS feed_language
            FROM item AS itm
            LEFT JOIN author AS auh ON auh.id = itm.author_id
            LEFT JOIN feed AS fed ON fed.id = itm.feed_id
            WHERE itm.content IS NOT NULL AND itm.id NOT IN (SELECT act_itm.item_id FROM action_item AS act_itm WHERE act_itm.item_id = itm.id AND act_itm.action_id = 11)
            ORDER BY itm.id DESC LIMIT 0,3000';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $result) {
                $action = 'POST';

                $path = '/'.$this->getIndex().'_item/doc/'.$result['id'];

                $body = [
                    'feed' => [
                        'id' => $result['feed_id'],
                        'title' => $result['feed_title'],
                        'language' => $result['feed_language'],
                    ],
                    'title' => $result['title'],
                    'content' => $result['content'],
                    'date' => $result['date'],
                ];
                if($result['author_id']) {
                    $body['author'] = [
                        'id' => $result['author_id'],
                        'title' => $result['author_title'],
                    ];
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

            $headers = [
                'Content-Type: application/json',
            ];

            if ($this->getUsername() && $this->getPassword()) {
                $headers[] = 'Authorization: Basic '.base64_encode($this->getUsername().':'.$this->getPassword());
            }

            $ci = curl_init();
            curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $action);
            curl_setopt($ci, CURLOPT_URL, $path);
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->getSslVerifyPeer());
            if($body) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, json_encode($body));
            }
            $exec = curl_exec($ci);
            if(false === $exec) {
                return curl_error($ci);
            } else {
                $result = json_decode($exec, true);
                if($action == 'HEAD') {
                    $result = curl_getinfo($ci, CURLINFO_HTTP_CODE);
                }
                //print_r($result);
                return $result;
            }
        }
    }

    public function init()
    {
        if($this->getEnabled()) {
            $path = '/'.$this->getIndex().'_feed';
            $result = $this->query('PUT', $path);

            $path = '/'.$this->getIndex().'_category';
            $result = $this->query('PUT', $path);

            $path = '/'.$this->getIndex().'_author';
            $result = $this->query('PUT', $path);

            $path = '/'.$this->getIndex().'_item';
            $result = $this->query('PUT', $path);

            $path = '/'.$this->getIndex().'_category/_mapping/doc';
            $body = [
                'doc' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                        ],
                        'date_created' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss',
                        ],
                    ],
                ],
            ];
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_author/_mapping/doc';
            $body = [
                'doc' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                        ],
                        'date_created' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss',
                        ],
                    ],
                ],
            ];
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_item/_mapping/doc';
            $body = [
                'doc' => [
                    'properties' => [
                        'feed' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'title' => [
                                    'type' => 'text',
                                ],
                                'language' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                        'author' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'title' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                        'title' => [
                            'type' => 'text',
                        ],
                        'content' => [
                            'type' => 'text',
                        ],
                        'date' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss',
                        ],
                    ],
                ],
            ];
            $result = $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_feed/_mapping/doc';
            $body = [
                'doc' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                        ],
                        'description' => [
                            'type' => 'text',
                        ],
                        'website' => [
                            'type' => 'text',
                        ],
                        'language' => [
                            'type' => 'text',
                        ],
                        'date_created' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss',
                        ],
                    ],
                ],
            ];
            $result = $this->query('PUT', $path, $body);
        }
    }

    public function reset()
    {
        if($this->getEnabled()) {
            $path = '/'.$this->getIndex();
            $result = $this->query('DELETE', $path);

            $types = ['author', 'category', 'feed', 'item'];
            foreach($types as $type) {
                $path = '/'.$this->getIndex().'_'.$type;
                $result = $this->query('DELETE', $path);

                if('feed' !== $type) {
                    $sql = 'DELETE FROM action_'.$type.' WHERE action_id = :action_id';
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue('action_id', 11);
                    $stmt->execute();
                }
            }
        }
    }
}
