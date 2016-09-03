<?php
namespace Axipi\FeedBundle\Manager;

use Axipi\FeedBundle\Manager\AbstractManager;
use Axipi\FeedBundle\Entity\Feed;
use Axipi\FeedBundle\Entity\Item;
use Axipi\FeedBundle\Entity\Author;
use Axipi\FeedBundle\Entity\Collection;
use Axipi\FeedBundle\Entity\CollectionFeed;

use Simplepie;
include('/Users/sdion/Sites/projects/sdion1/sdion1/vendor/simplepie/simplepie/library/SimplePie.php');

if( ! function_exists('convert_to_ascii')) {
	function convert_to_ascii($url) {
		$parts = parse_url($url);
		if(!isset($parts['host'])) {
			return $url;
		}
		if(mb_detect_encoding($parts['host']) != 'ASCII') {
			$url = str_replace($parts['host'], idn_to_ascii($parts['host']), $url);
		}
		return $url;
	}
}

class FeedManager extends AbstractManager
{
    public function insert($table, $fields) {
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($fields)).') VALUES ('.implode(', ', array_map(function($n) {return ':'.$n;}, array_keys($fields))).')';
        $stmt = $this->em->getConnection()->prepare($sql);
        foreach($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $this->em->getConnection()->lastInsertId();
    }

    public function update($table, $fields, $where) {
        $sql = 'UPDATE '.$table.' SET '.implode(', ', array_map(function($n) {return $n.' = :'.$n;}, array_keys($fields))).' WHERE '.implode(', ', array_map(function($n) {return $n.' = :'.$n;}, array_keys($where)));
        $stmt = $this->em->getConnection()->prepare($sql);
        foreach($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        foreach($where as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
    }

    public function updateAll()
    {
        /*$query = $this->em->createQueryBuilder();
        $query->addSelect('fed');
        $query->from('AxipiFeedBundle:Feed', 'fed');*/

        /*$query->leftJoin('clc_fed.component', 'cmp');
        $query->andWhere('fed.error = :active');
        $query->setParameter(':active', true);*/

        //$getQuery = $query->getQuery();

        $sql = 'SELECT * FROM feed';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $feeds_result = $stmt->fetchAll();

        $feeds = 0;
        $errors = 0;
        $time = 0;
        $memory = 0;

        $collection = [
            'feeds' => $feeds,
            'errors' => $errors,
            'time' => $time,
            'memory' => $memory,
            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
        $collection_id = $this->insert('collection', $collection);

        $u = 1;
        foreach ($feeds_result as $feed) {
            $feeds++;
            echo $feed['title']."<br>\r\n";

            $collectionFeed = [
                'collection_id' => $collection_id,
                'feed_id' => $feed['id'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];

            try {
                $sp_feed = new Simplepie();
                $sp_feed->set_feed_url(convert_to_ascii($feed['link']));
                $sp_feed->enable_cache(false);
                $sp_feed->set_timeout(5);
                $sp_feed->force_feed(true);
                $sp_feed->init();
                $sp_feed->handle_content_type();

                if($sp_feed->error()) {
                    $errors++;
                    echo $sp_feed->error()."<br>\r\n";
                    $collectionFeed['error'] = $sp_feed->error();
                }

                if(!$sp_feed->error()) {
                    $items = $sp_feed->get_items();

                    foreach($items as $sp_item) {
                        $link = str_replace('&amp;', '&', $sp_item->get_link());

                        $sql = 'SELECT COUNT(*) AS count FROM item WHERE link = :link';
                        $stmt = $this->em->getConnection()->prepare($sql);
                        $stmt->bindValue('link', $link);
                        $stmt->execute();
                        $result = $stmt->fetch();

                        if($result['count'] > 0) {
                            continue;
                        }

                        if($sp_author = $sp_item->get_author()) {
                            if($sp_author->get_name() != '') {
                                $sql = 'SELECT id FROM author WHERE title = :title';
                                $stmt = $this->em->getConnection()->prepare($sql);
                                $stmt->bindValue('title', $sp_author->get_name());
                                $stmt->execute();
                                $result = $stmt->fetch();

                                if($result) {
                                    $author_id = $result['id'];
                                } else {
                                    $author = [
                                        'title' => $sp_author->get_name(),
                                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                                    ];
                                    $author_id = $this->insert('author', $author);
                                }
                            } else {
                                $author_id = false;
                            }

                        } else {
                            $author_id = false;
                        }

                        $item = [];

                        $item['feed_id'] = $feed['id'];

                        if($sp_item->get_title()) {
                            $item['title'] = $sp_item->get_title();
                        } else {
                            $item['title'] = '-';
                        }

                        if($author_id) {
                            $item['author_id'] = $author_id;
                        }

                        $item['link'] = $link;

                        if($sp_item->get_content()) {
                            $content = preg_replace('/[^(\x20-\x7F)]*/', '', $sp_item->get_content());
                            $item['content']  = $content;
                        } else {
                            $item['content'] = '-';
                        }

                        if($sp_item->get_latitude() && $sp_item->get_longitude()) {
                            $item['latitude'] = $sp_item->get_latitude();
                            $item['longitude'] = $sp_item->get_longitude();
                        }

                        $sp_itm_date = $sp_item->get_gmdate('Y-m-d H:i:s');
                        if($sp_itm_date) {
                            $item['date'] = $sp_itm_date;
                        } else {
                            $item['date'] = (new \Datetime())->format('Y-m-d H:i:s');
                        }

                        $item['date_created'] = (new \Datetime())->format('Y-m-d H:i:s');

                        $this->insert('item', $item);
                    }

                    $parse_url = parse_url($sp_feed->get_link());

                    $feed_update = [];
                    $feed_update['title'] = $sp_feed->get_title();
                    $feed_update['website'] = $sp_feed->get_link();
                    $feed_update['link'] = $sp_feed->subscribe_url();
                    if(isset($parse_url['host']) == 1) {
                        $feed_update['hostname'] = $parse_url['host'];
                    }
                    $feed_update['description'] = $sp_feed->get_description();
                    $this->update('feed', $feed_update, ['id' => $feed['id']]);
                }
            } catch (Exception $e) {
                $errors++;
                $collectionFeed['error'] = $e->getMessage();
            }

            $this->insert('collection_feed', $collectionFeed);

            echo number_format(memory_get_peak_usage(), 0, '.', ' ')."<br>\r\n";

            if($u == 50) {
                break;
            } else {
                $u++;
            }
        }
        echo number_format(memory_get_peak_usage(), 0, '.', ' ')."<br>\r\n";

        $collection_update = [];
        $collection_update['feeds'] = $feeds;
        $collection_update['errors'] = $errors;
        $collection_update['memory'] = memory_get_peak_usage();
        $this->update('collection', $collection_update, ['id' => $collection_id]);
    }
}
