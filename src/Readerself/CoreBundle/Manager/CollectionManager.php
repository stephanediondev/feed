<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Collection;
use Readerself\CoreBundle\Event\CollectionEvent;

use Simplepie;
use Facebook;

class CollectionManager extends AbstractManager
{
    protected $simplepie;

    protected $pushManager;

    protected $facebookEnabled;

    protected $facebookId;

    protected $facebookSecret;

    public function __construct(
        Simplepie $simplepie,
        PushManager $pushManager
    ) {
        $this->simplepie = $simplepie;
        $this->pushManager = $pushManager;

        $this->cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Collection')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Collection')->getList($parameters);
    }

    public function init()
    {
        $collection = new Collection();
        $collection->setFeeds(0);
        $collection->setErrors(0);
        $collection->setTime(0);
        $collection->setMemory(0);
        return $collection;
    }

    public function persist($data)
    {
        if($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new CollectionEvent($data, $mode);
        $this->eventDispatcher->dispatch('Collection.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new CollectionEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Collection.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }

    public function setFacebook($enabled, $id, $secret)
    {
        $this->facebookEnabled = $enabled;
        $this->facebookId = $id;
        $this->facebookSecret = $secret;
    }

    public function start()
    {
        $startTime = microtime(1);

        if($this->facebookEnabled) {
            $fb = new Facebook\Facebook(array(
                'app_id' => $this->facebookId,
                'app_secret' => $this->facebookSecret,
            ));
            $fbApp = $fb->getApp();
            $accessToken = $fbApp->getAccessToken();
        }

        /*foreach($this->pushManager->getList([]) as $push) {
            $payload = json_encode(array(
                'title' => 'title dyn '.$push->getId(),
                'body' => $push->getAgent(),
            ));
            $this->pushManager->send($push, $payload);
        }
        exit(0);*/

        $sql = 'SELECT id, link FROM feed LIMIT 200,150';
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
            'date_modified' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
        $collection_id = $this->insert('collection', $insertCollection);

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

            } else if(isset($parse_url['host']) == 1 && $parse_url['host'] == 'www.facebook.com' && $this->facebookEnabled) {
                try {
                    $parts = explode('/', rtrim($parse_url['path'], '/'));
                    $total_parts = count($parts);
                    $last_part = $parts[$total_parts - 1 ];
                    $request = new Facebook\FacebookRequest($fbApp, $accessToken, 'GET', $last_part.'?fields=link,name,about');
                    $response = $fb->getClient()->sendRequest($request);
                    $result = $response->getDecodedBody();

                    $request = new Facebook\FacebookRequest($fbApp, $accessToken, 'GET', $last_part.'?fields=feed{created_time,id,message,story,full_picture,place,type,status_type,link,name}');
                    $response = $fb->getClient()->sendRequest($request);
                    $posts = $response->getDecodedBody();
                    $this->setItemsFacebook($feed, $posts['feed']['data']);

                    $updateFeed = [];
                    $updateFeed['title'] = $this->cleanTitle($result['name']);
                    $updateFeed['website'] = $result['link'];
                    $updateFeed['link'] = $this->cleanLink($result['link']);
                    if(isset($parse_url['host']) == 1) {
                        $updateFeed['hostname'] = $parse_url['host'];
                    }
                    $updateFeed['description'] = $result['about'];

                    $updateFeed['next_collection'] = $this->setNextCollection($feed);

                    $updateFeed['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');

                    $this->update('feed', $updateFeed, $feed['id']);

                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    $errors++;
                    $insertCollectionFeed['error'] = $e->getMessage();

                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    $errors++;
                    $insertCollectionFeed['error'] = $e->getMessage();
                }

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
                        $updateFeed['title'] = $this->cleanTitle($sp_feed->get_title());
                        $updateFeed['website'] = $sp_feed->get_link();
                        $updateFeed['link'] = $this->cleanLink($sp_feed->subscribe_url());
                        if(isset($parse_url['host']) == 1) {
                            $updateFeed['hostname'] = $parse_url['host'];
                        }
                        $updateFeed['description'] = $sp_feed->get_description();

                        $updateFeed['next_collection'] = $this->setNextCollection($feed);

                        $updateFeed['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');

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
        }

        $updateCollection = [];
        $updateCollection['feeds'] = $feeds;
        $updateCollection['errors'] = $errors;
        $updateCollection['time'] = microtime(1) - $startTime;
        $updateCollection['memory'] = memory_get_peak_usage();
        $updateCollection['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');
        $this->update('collection', $updateCollection, $collection_id);
    }

    public function setNextCollection($feed)
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
        return null;
    }

    public function setItems($feed, $items)
    {
        foreach($items as $sp_item) {
            $link = $this->cleanLink($sp_item->get_link());

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
                $insertItem['title'] = $this->cleanTitle($sp_item->get_title());
            } else {
                $insertItem['title'] = '-';
            }

            $insertItem['author_id'] = $this->setAuthor($sp_item);

            $insertItem['link'] = $link;

            if($content = $sp_item->get_content()) {
                if(class_exists('Tidy')) {
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
                        $tidy->parseString($content, $options, 'utf8');
                        $tidy->cleanRepair();
                        $content = $tidy;
    
                        if(class_exists('DOMDocument')) {
                            try {
                                libxml_use_internal_errors(true);

                                $dom = new \DOMDocument;
                                $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                $xpath = new \DOMXPath($dom);
    
                                $disallowedAttributes = ['id', 'class', 'style', 'onclick', 'ondblclick', 'onmouseover', 'onmouseout', 'accesskey', 'data', 'dynsrc', 'tabindex'];
                                foreach($disallowedAttributes as $attribute) {
                                    $nodes = $xpath->query('//*[@'.$attribute.']');
                                    foreach($nodes as $node) {
                                        $node->removeAttribute($attribute);
                                    }
                                }
                                $content = $dom->saveHTML();

                                libxml_clear_errors();
                            } catch (Exception $e) {
                            }
                        }
                    } catch (Exception $e) {
                    }
                }

                $insertItem['content']  = $content;
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
            $insertItem['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');

            $item_id = $this->insert('item', $insertItem);

            $this->setCategories($item_id, $sp_item->get_categories());

            $this->setEnclosures($item_id, $sp_item->get_enclosures());

            unset($sp_item);
        }
    }

    public function setItemsFacebook($feed, $items)
    {
        foreach($items as $sp_item) {
            if(isset($sp_item['link']) == 0) {
                continue;
            }

            $link = $this->cleanLink($sp_item['link']);

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

            if(isset($sp_item['story'])) {
                $insertItem['title'] = $this->cleanTitle($sp_item['story']);
            } else if(isset($sp_item['name'])) {
                $insertItem['title'] = $this->cleanTitle($sp_item['name']);
            } else {
                $insertItem['title'] = '-';
            }

            $insertItem['link'] = $link;

            if(isset($sp_item['message']) == 1) {
                $insertItem['content']  = nl2br($sp_item['message']);
            } else {
                $insertItem['content'] = '-';
            }

            if(isset($sp_item['place'])) {
                if($sp_item['place']['location']['latitude'] && $sp_item['place']['location']['longitude']) {
                    $insertItem['latitude'] = $sp_item['place']['location']['latitude'];
                    $insertItem['longitude'] = $sp_item['place']['location']['longitude'];
                }
            }

            if($date = $sp_item['created_time']) {
                $insertItem['date'] = (new \Datetime($date))->format('Y-m-d H:i:s');;
            } else {
                $insertItem['date'] = (new \Datetime())->format('Y-m-d H:i:s');
            }

            $insertItem['date_created'] = (new \Datetime())->format('Y-m-d H:i:s');
            $insertItem['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');

            $item_id = $this->insert('item', $insertItem);

            if(isset($sp_item['full_picture']) == 1) {
                $insertEnclosure = [
                    'item_id' => $item_id,
                    'link' => $this->cleanLink($sp_item['full_picture']),
                    'type' => 'image/jpeg',
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('enclosure', $insertEnclosure);
            }

            unset($sp_item);
        }
    }

    public function setAuthor($sp_item)
    {
        $author_id = null;

        if($sp_author = $sp_item->get_author()) {
            if($sp_author->get_name() != '') {
                $title = $this->cleanTitle($sp_author->get_name());

                $cache_id = 'readerself.author_title.'.$title;

                if($this->cacheDriver->contains($cache_id)) {
                    $author_id = $this->cacheDriver->fetch($cache_id);
    
                } else {
                    $sql = 'SELECT id FROM author WHERE title = :title';
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue('title', $title);
                    $stmt->execute();
                    $result = $stmt->fetch();

                    if($result) {
                        $author_id = $result['id'];
                    } else {
                        $insertAuthor = [
                            'title' => $title,
                            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        ];
                        $author_id = $this->insert('author', $insertAuthor);
                    }
                    $this->cacheDriver->save($cache_id, $author_id);
                }
            }
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
                        foreach($categoriesPart as $title) {
                            $title = mb_strtolower($title, 'UTF-8');
                            $title = $this->cleanTitle($title);
                            if($title != '') {
                                $titles[] = $title;
                            }
                        }
                    } else {
                        $title = mb_strtolower($sp_category->get_label(), 'UTF-8');
                        $title = $this->cleanTitle($title);
                        if($title != '') {
                            $titles[] = $title;
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

    public function setCategory($title)
    {
        $cache_id = 'readerself.category_title.'.$title;

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
            $this->cacheDriver->save($cache_id, $category_id);
        }
        return $category_id;
    }

    public function setEnclosures($item_id, $enclosures)
    {
        if($enclosures) {
            $links = [];
            foreach($enclosures as $sp_enclosure) {
                if($sp_enclosure->get_link() && $sp_enclosure->get_type()) {
                    $link = $this->cleanLink($sp_enclosure->get_link());

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

    public function cleanLink($link)
    {
        $link = str_replace('&amp;', '&', $link);
        $link = mb_substr($link, 0, 255, 'UTF-8');

        return $link;
    }

    public function cleanTitle($title)
    {
        $title = trim( strip_tags( html_entity_decode( $title ) ) );
        $title = str_replace('&amp;', '&', $title);
        $title = mb_substr($title, 0, 255, 'UTF-8');

        return $title;
    }
}
