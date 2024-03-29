<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Repository\ActionRepository;

class SearchManager extends AbstractManager
{
    private bool $elasticsearchEnabled;

    private string $elasticsearchIndex;

    private string $elasticsearchUrl;

    private string $elasticsearchUsername;

    private string $elasticsearchPassword;

    private string $elasticsearchApiKey;

    private bool $sslVerifyPeer;

    private bool $sslVerifyHost;

    private ActionRepository $actionRepository;

    public function __construct(
        bool $elasticsearchEnabled,
        string $elasticsearchIndex,
        string $elasticsearchUrl,
        string $elasticsearchUsername,
        string $elasticsearchPassword,
        string $elasticsearchApiKey,
        bool $sslVerifyPeer,
        bool $sslVerifyHost,
        ActionRepository $actionRepository
    ) {
        $this->elasticsearchEnabled = $elasticsearchEnabled;
        $this->elasticsearchIndex = $elasticsearchIndex;
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->elasticsearchUsername = $elasticsearchUsername;
        $this->elasticsearchPassword = $elasticsearchPassword;
        $this->elasticsearchApiKey = $elasticsearchApiKey;
        $this->sslVerifyPeer = $sslVerifyPeer;
        $this->sslVerifyHost = $sslVerifyHost;
        $this->actionRepository = $actionRepository;
    }

    public function getEnabled(): bool
    {
        return $this->elasticsearchEnabled;
    }

    public function getIndex(): string
    {
        return $this->elasticsearchIndex;
    }

    public function getUrl(): string
    {
        return $this->elasticsearchUrl;
    }

    public function getUsername(): string
    {
        return $this->elasticsearchUsername;
    }

    public function getPassword(): string
    {
        return $this->elasticsearchPassword;
    }

    public function getApiKey(): string
    {
        return $this->elasticsearchApiKey;
    }

    public function getSslVerifyPeer(): bool
    {
        return $this->sslVerifyPeer;
    }

    public function getSslVerifyHost(): int
    {
        return $this->sslVerifyHost ? 2 : 0;
    }

    public function start(): void
    {
        if ($this->getEnabled()) {
            //feeds
            $sql = 'SELECT fed.* FROM feed AS fed';
            $stmt = $this->actionRepository->getConnection()->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            $body = [];
            foreach ($results as $result) {
                $body[] = json_encode(['index' => ['_id' => $result['id']]]);

                $line = [
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'website' => $result['website'],
                    'language' => $result['language'],
                    'date_created' => $result['date_created'],
                ];
                $body[] = json_encode($line);
            }

            $path = '/'.$this->getIndex().'_feed/_bulk';
            $this->query('POST', $path, $body, true);

            //categories
            $sql = 'SELECT cat.*
            FROM category AS cat
            WHERE cat.id NOT IN (SELECT act_cat.category_id FROM action_category AS act_cat WHERE act_cat.category_id = cat.id AND act_cat.action_id = 11)
            ORDER BY cat.id DESC LIMIT 0,1000';
            $stmt = $this->actionRepository->getConnection()->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            $body = [];
            foreach ($results as $result) {
                $body[] = json_encode(['index' => ['_id' => $result['id']]]);

                $line = [
                    'title' => $result['title'],
                    'date_created' => $result['date_created'],
                ];
                $body[] = json_encode($line);

                $insertActionCategory = [
                    'category_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->actionRepository->insert('action_category', $insertActionCategory);
            }

            $path = '/'.$this->getIndex().'_category/_bulk';
            $this->query('POST', $path, $body, true);

            //authors
            $sql = 'SELECT aut.*
            FROM author AS aut
            WHERE aut.id NOT IN (SELECT act_aut.author_id FROM action_author AS act_aut WHERE act_aut.author_id = aut.id AND act_aut.action_id = 11)
            ORDER BY aut.id DESC LIMIT 0,1000';
            $stmt = $this->actionRepository->getConnection()->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            $body = [];
            foreach ($results as $result) {
                $body[] = json_encode(['index' => ['_id' => $result['id']]]);

                $line = [
                    'title' => $result['title'],
                    'date_created' => $result['date_created'],
                ];
                $body[] = json_encode($line);

                $insertActionCategory = [
                    'author_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->actionRepository->insert('action_author', $insertActionCategory);
            }

            $path = '/'.$this->getIndex().'_author/_bulk';
            $this->query('POST', $path, $body, true);

            //items
            $sql = 'SELECT itm.*, auh.title AS author_title, fed.title AS feed_title, fed.language AS feed_language
            FROM item AS itm
            LEFT JOIN author AS auh ON auh.id = itm.author_id
            LEFT JOIN feed AS fed ON fed.id = itm.feed_id
            WHERE itm.content IS NOT NULL AND itm.id NOT IN (SELECT act_itm.item_id FROM action_item AS act_itm WHERE act_itm.item_id = itm.id AND act_itm.action_id = 11)
            ORDER BY itm.id DESC LIMIT 0,1000';
            $stmt = $this->actionRepository->getConnection()->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            $body = [];
            foreach ($results as $result) {
                $body[] = json_encode(['index' => ['_id' => $result['id']]]);

                $line = [
                    'feed' => [
                        'id' => $result['feed_id'],
                        'title' => $result['feed_title'],
                        'language' => $result['feed_language'],
                    ],
                    'title' => $result['title'],
                    'content' => $result['content'],
                    'date' => $result['date'],
                ];
                if ($result['author_id']) {
                    $line['author'] = [
                        'id' => $result['author_id'],
                        'title' => $result['author_title'],
                    ];
                }
                $body[] = json_encode($line);

                $insertActionItem = [
                    'item_id' => $result['id'],
                    'action_id' => 11,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->actionRepository->insert('action_item', $insertActionItem);
            }

            $path = '/'.$this->getIndex().'_item/_bulk';
            $this->query('POST', $path, $body, true);
        }
    }

    /**
     * @param array<mixed>|null $body
     */
    public function query(string $action, string $path, ?array $body = null, bool $ndjson = false): mixed
    {
        if ($this->getEnabled()) {
            $path = $this->getUrl().$path;

            $headers = [
                'Content-Type: application/json',
            ];

            if ($this->getApiKey()) {
                $headers[] = 'Authorization: ApiKey '.$this->getApiKey();
            } elseif ($this->getUsername() && $this->getPassword()) {
                $headers[] = 'Authorization: Basic '.base64_encode($this->getUsername().':'.$this->getPassword());
            }

            $ci = curl_init();
            curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $action);
            curl_setopt($ci, CURLOPT_URL, $path);
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->getSslVerifyPeer());
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, $this->getSslVerifyHost());
            if (null !== $body && true === is_array($body) && 0 < count($body)) {
                if (true === $ndjson) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, implode("\r\n", $body)."\r\n");
                } else {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, json_encode($body));
                }
            }
            $exec = curl_exec($ci);
            if ($exec && is_string($exec)) {
                $result = json_decode($exec, true);
                if ($action == 'HEAD') {
                    $result = curl_getinfo($ci, CURLINFO_HTTP_CODE);
                }
                return $result;
            } else {
                return curl_error($ci);
            }
        }

        return null;
    }

    public function create(): void
    {
        if ($this->getEnabled()) {
            $body = [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ];
            $path = '/'.$this->getIndex().'_feed';
            $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_category';
            $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_author';
            $this->query('PUT', $path, $body);

            $path = '/'.$this->getIndex().'_item';
            $this->query('PUT', $path, $body);

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
            $this->query('PUT', $path, $body);

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
            $this->query('PUT', $path, $body);

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
            $this->query('PUT', $path, $body);

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
            $this->query('PUT', $path, $body);
        }
    }

    public function remove(): void
    {
        if ($this->getEnabled()) {
            $types = [
                'author' => 'DELETE FROM action_author WHERE action_id = :action_id',
                'category' => 'DELETE FROM action_category WHERE action_id = :action_id',
                'feed' => null,
                'item' => 'DELETE FROM action_item WHERE action_id = :action_id',
            ];
            foreach ($types as $type => $sql) {
                $path = '/'.$this->getIndex().'_'.$type;
                $this->query('DELETE', $path);

                if (null !== $sql) {
                    $stmt = $this->actionRepository->getConnection()->prepare($sql);
                    $stmt->bindValue('action_id', 11);
                    $stmt->executeQuery();
                }
            }
        }
    }
}
