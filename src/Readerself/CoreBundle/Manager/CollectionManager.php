<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use Simplepie;

class CollectionManager extends AbstractManager
{
    protected $simplepie;

    public function __construct(
        Simplepie $simplepie
    ) {
        $this->simplepie = $simplepie;

        $this->cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
    }

    public function start()
    {
        $startTime = microtime(1);

        $sql = 'SELECT id, link FROM feed LIMIT 420,30';//1311 latin1 //1179 utf8 4 bytes
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $feeds_result = $stmt->fetchAll();

        $feeds = 0;
        $errors = 0;
        $time = 0;
        $memory = 0;

        $insertCollection = [
            'feeds' => $feeds,
            'errors' => $errors,
            'time' => $time,
            'memory' => $memory,
            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
        $collection_id = $this->insert('collection', $insertCollection);

        $u = 1;
        foreach ($feeds_result as $feed) {
            $feeds++;

            $insertCollectionFeed = [
                'collection_id' => $collection_id,
                'feed_id' => $feed['id'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];

            $parse_url = parse_url($feed['link']);

            if(isset($parse_url['scheme']) == 0 || ($parse_url['scheme'] != 'http' && $parse_url['scheme'] != 'https')) {
                $errors++;
                $insertCollectionFeed['error'] = 'Unvalid scheme';

            } else if(isset($parse_url['host']) == 1 && $parse_url['host'] == 'instagram.com') {

            } else if(isset($parse_url['host']) == 1 && $parse_url['host'] == 'www.facebook.com') {

            } else {
                try {
                    $sp_feed = clone $this->simplepie;
                    $sp_feed->set_feed_url($this->toAscii($feed['link']));
                    $sp_feed->enable_cache(false);
                    $sp_feed->set_timeout(5);
                    $sp_feed->force_feed(true);
                    $sp_feed->init();
                    $sp_feed->handle_content_type();

                    if($sp_feed->error()) {
                        $errors++;
                        $insertCollectionFeed['error'] = $sp_feed->error();
                    }

                    if(!$sp_feed->error()) {
                        $this->setItems($feed, $sp_feed->get_items());

                        $parse_url = parse_url($sp_feed->get_link());

                        $updateFeed = [];
                        $updateFeed['title'] = $sp_feed->get_title();
                        $updateFeed['website'] = $sp_feed->get_link();
                        $updateFeed['link'] = $sp_feed->subscribe_url();
                        if(isset($parse_url['host']) == 1) {
                            $updateFeed['hostname'] = $parse_url['host'];
                        }
                        $updateFeed['description'] = $sp_feed->get_description();

                        if($nextCollection = $this->getLastItem($feed)) {
                            $updateFeed['next_collection'] = $nextCollection;
                        }

                        $this->update('feed', $updateFeed, $feed['id']);
                    }
                    $sp_feed->__destruct();
                    unset($sp_feed);
                } catch (Exception $e) {
                    $errors++;
                    $insertCollectionFeed['error'] = $e->getMessage();
                }
            }

            $this->insert('collection_feed', $insertCollectionFeed);

            echo number_format(memory_get_peak_usage(), 0, '.', ' ')."<br>\r\n";

            if($u == 100) {
                break;
            } else {
                $u++;
            }
        }
        echo number_format(memory_get_peak_usage(), 0, '.', ' ')."<br>\r\n";

        $updateCollection = [];
        $updateCollection['feeds'] = $feeds;
        $updateCollection['errors'] = $errors;
        $updateCollection['time'] = microtime(1) - $startTime;
        $updateCollection['memory'] = memory_get_peak_usage();
        $this->update('collection', $updateCollection, $collection_id);
    }

    public function getLastItem($feed)
    {
        $sql = 'SELECT date_created FROM item WHERE feed_id = :feed_id GROUP BY id ORDER BY id DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('feed_id', $feed['id']);
        $stmt->execute();
        $result = $stmt->fetch();

        if($result) {
            //older than 96 hours, next collection in 12 hours
            if($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 96)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 12));
                return $nextCollection->format('Y-m-d H:i:s');

            //older than 48 hours, next collection in 6 hours
            } else if($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 48)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 6));
                return $nextCollection->format('Y-m-d H:i:s');

            //older than 24 hours, next collection in 3 hours
            } else if($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 24)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 3));
                return $nextCollection->format('Y-m-d H:i:s');
            }
        }
        return false;
    }

    public function setItems($feed, $items)
    {
        foreach($items as $sp_item) {
            $link = str_replace('&amp;', '&', $sp_item->get_link());
            $link = mb_substr($link, 0, 255, 'UTF-8');

            $sql = 'SELECT id FROM item WHERE link = :link';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('link', $link);
            $stmt->execute();
            $result = $stmt->fetch();

            if($result) {
                break;
            }

            $insertItem = [];

            $insertItem['feed_id'] = $feed['id'];

            if($sp_item->get_title()) {
                $insertItem['title'] = mb_substr($sp_item->get_title(), 0, 255, 'UTF-8');
            } else {
                $insertItem['title'] = '-';
            }

            $insertItem['author_id'] = $this->setAuthor($sp_item);

            $insertItem['link'] = $link;

            if($sp_item->get_content()) {
                $insertItem['content']  = $sp_item->get_content();
            } else {
                $insertItem['content'] = '-';
            }

            if($sp_item->get_latitude() && $sp_item->get_longitude()) {
                $insertItem['latitude'] = $sp_item->get_latitude();
                $insertItem['longitude'] = $sp_item->get_longitude();
            }

            if($date = $sp_item->get_gmdate('Y-m-d H:i:s')) {
                $insertItem['date'] = $date;
            } else {
                $insertItem['date'] = (new \Datetime())->format('Y-m-d H:i:s');
            }

            $insertItem['date_created'] = (new \Datetime())->format('Y-m-d H:i:s');

            $item_id = $this->insert('item', $insertItem);

            $this->setCategories($item_id, $sp_item->get_categories());

            $this->setEnclosures($item_id, $sp_item->get_enclosures());

            unset($sp_item);
        }
    }

    public function setAuthor($sp_item)
    {
        $author_id = null;

        if($sp_author = $sp_item->get_author()) {
            if($sp_author->get_name() != '') {
                $cache_id = 'readerself/author/'.$sp_author->get_name();

                if($this->cacheDriver->contains($cache_id)) {
                    $author_id = $this->cacheDriver->fetch($cache_id);
    
                } else {
                    $sql = 'SELECT id FROM author WHERE title = :title';
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue('title', $sp_author->get_name());
                    $stmt->execute();
                    $result = $stmt->fetch();

                    if($result) {
                        $author_id = $result['id'];
                    } else {
                        $insertAuthor = [
                            'title' => $sp_author->get_name(),
                            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        ];
                        $author_id = $this->insert('author', $insertAuthor);
                    }
                    echo $cache_id."<br>\r\n";
                    $this->cacheDriver->save($cache_id, $author_id);
                }
            }
            unset($sp_author);
        }
        unset($sp_item);

        return $author_id;
    }

    public function setCategories($item_id, $categories)
    {
        if($categories) {
            $titles = [];
            foreach($categories as $sp_category) {
                if($sp_category->get_label()) {
                    if(strstr($sp_category->get_label(), ',')) {
                        $categoriesPart = explode(',', $sp_category->get_label());
                        foreach($categoriesPart as $categoryPart) {
                            $categoryPart = trim( strip_tags( html_entity_decode( $categoryPart ) ) );
                            if($categoryPart != '') {
                                $titles[] = mb_strtolower($categoryPart, 'UTF-8');
                            }
                        }
                    } else {
                        $categoryPart = trim( strip_tags( html_entity_decode( $sp_category->get_label() ) ) );
                        if($categoryPart != '') {
                            $titles[] = mb_strtolower($categoryPart, 'UTF-8');
                        }
                    }
                }
                unset($sp_category);
            }

            $titles = array_unique($titles);
            foreach($titles as $title) {
                $insertItemCategory = [
                    'item_id' => $item_id,
                    'category_id' => $this->setCategory($title),
                ];
                $this->insert('item_category', $insertItemCategory);

            }
            unset($titles);
        }
    }

    public function setCategory($title) {
        $cache_id = 'readerself/category/'.$title;

        if($this->cacheDriver->contains($cache_id)) {
            $category_id = $this->cacheDriver->fetch($cache_id);

        } else {
            $sql = 'SELECT id FROM category WHERE title = :title';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('title', $title);
            $stmt->execute();
            $result = $stmt->fetch();

            if($result) {
                $category_id = $result['id'];
            } else {
                $insertCategory = [
                    'title' => $title,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $category_id = $this->insert('category', $insertCategory);
            }
            echo $cache_id."<br>\r\n";
            $this->cacheDriver->save($cache_id, $category_id);
        }
        return $category_id;
    }

    public function setEnclosures($item_id, $enclosures)
    {
        if($enclosures) {
            $links = [];
            foreach($enclosures as $sp_enclosure) {
                if($sp_enclosure->get_link() && $sp_enclosure->get_type() && $sp_enclosure->get_length()) {
                    $link = $sp_enclosure->get_link();
                    if(substr($link, -2) == '?#') {
                        $link = substr($link, 0, -2);
                    }
                    if(!in_array($link, $links)) {
                        $insertEnclosure = [
                            'item_id' => $item_id,
                            'link' => $link,
                            'type' => $sp_enclosure->get_type(),
                            'length' => $sp_enclosure->get_length(),
                            'width' => $sp_enclosure->get_width(),
                            'height' => $sp_enclosure->get_height(),
                            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        ];
                        $this->insert('enclosure', $insertEnclosure);

                        $links[] = $link;
                    }
                }
                unset($sp_enclosure);
            }
            unset($links);
        }
    }
}
