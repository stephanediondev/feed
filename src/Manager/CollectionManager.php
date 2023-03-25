<?php

namespace App\Manager;

use App\Entity\Collection;
use App\Event\CollectionEvent;
use App\Manager\AbstractManager;
use App\Repository\CollectionRepository;
use SimplePie\SimplePie;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\String\Slugger\AsciiSlugger;

class CollectionManager extends AbstractManager
{
    private CollectionRepository $collectionRepository;

    protected MemberManager $memberManager;

    protected $cacheDriver;

    public function __construct(CollectionRepository $collectionRepository, MemberManager $memberManager)
    {
        $this->collectionRepository = $collectionRepository;
        $this->memberManager = $memberManager;

        $this->cacheDriver = new ApcuAdapter();
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getOne(array $parameters = []): ?Collection
    {
        return $this->collectionRepository->getOne($parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getList(array $parameters = []): mixed
    {
        return $this->collectionRepository->getList($parameters);
    }

    public function init(): Collection
    {
        $collection = new Collection();
        $collection->setFeeds(0);
        $collection->setErrors(0);
        $collection->setTime(0);
        $collection->setMemory(0);
        return $collection;
    }

    public function persist(Collection $data): int
    {
        if ($data->getDateCreated() === null) {
            $eventName = CollectionEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = CollectionEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->collectionRepository->persist($data);

        $event = new CollectionEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove(Collection $data): void
    {
        $event = new CollectionEvent($data);
        $this->eventDispatcher->dispatch($event, CollectionEvent::DELETED);

        $this->collectionRepository->remove($data);

        $this->clearCache();
    }

    public function start($feed_id = false): void
    {
        $startTime = microtime(true);

        $feeds = 0;
        $errors = 0;

        if ($feed_id) {
            $sql = 'SELECT id, link FROM feed WHERE id = :feed_id';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('feed_id', $feed_id);
        } else {
            $sql = 'SELECT id, link FROM feed WHERE next_collection IS NULL OR next_collection <= :date';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('date', (new \Datetime())->format('Y-m-d H:i:s'));
        }
        $resultSet = $stmt->executeQuery();
        $feeds_result = $resultSet->fetchAllAssociative();

        $collection = $this->init();
        $collection->setFeeds(count($feeds_result));
        $collection_id = $this->persist($collection);

        foreach ($feeds_result as $feed) {
            $feeds++;

            $insertCollectionFeed = [
                'collection_id' => $collection_id,
                'feed_id' => $feed['id'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];

            $parse_url = parse_url($feed['link']);

            if (isset($parse_url['scheme']) == 0 || ($parse_url['scheme'] != 'http' && $parse_url['scheme'] != 'https')) {
                $errors++;
                $insertCollectionFeed['error'] = 'Unvalid scheme';
            } else {
                try {
                    $sp_feed = new SimplePie();
                    $stripTagsNew = [];
                    foreach ($sp_feed->sanitize->strip_htmltags as $tag) {
                        if ($tag != 'iframe') {
                            $stripTagsNew[] = $tag;
                        }
                    }
                    $sp_feed->sanitize->strip_htmltags = $stripTagsNew;
                    $sp_feed->set_feed_url($this->toAscii($feed['link']));
                    $sp_feed->enable_cache(false);
                    $sp_feed->set_timeout(15);
                    $sp_feed->force_feed(true);
                    $sp_feed->init();
                    $sp_feed->handle_content_type();
                    $sp_feed->set_curl_options([
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_SSL_VERIFYPEER => false
                    ]);

                    if ($sp_feed->error()) {
                        $errors++;
                        $insertCollectionFeed['error'] = $sp_feed->error();
                    }

                    if (!$sp_feed->error()) {
                        $this->setItems($feed, $sp_feed->get_items());

                        $parse_url = parse_url($sp_feed->get_link());

                        $updateFeed = [];
                        $updateFeed['title'] = $sp_feed->get_title() != '' ? $this->cleanTitle($sp_feed->get_title()) : '-';
                        $updateFeed['website'] = $this->cleanWebsite($sp_feed->get_link());
                        $updateFeed['link'] = $this->cleanLink($sp_feed->subscribe_url());
                        $updateFeed['hostname'] = isset($parse_url['host']) ? $parse_url['host'] : null;
                        $updateFeed['description'] = $sp_feed->get_description();

                        $updateFeed['language'] = $sp_feed->get_language() ? substr($sp_feed->get_language(), 0, 2) : null;

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

        $collection->setFeeds($feeds);
        $collection->setErrors($errors);
        $collection->setTime(microtime(true) - $startTime);
        $collection->setMemory(memory_get_peak_usage());
        $this->persist($collection);

        $sql = 'SELECT id FROM member';
        $stmt = $this->connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $members_result = $resultSet->fetchAllAssociative();

        foreach ($members_result as $member) {
            $this->memberManager->syncUnread($member['id']);
        }
    }

    public function setNextCollection(array $feed): ?string
    {
        $sql = 'SELECT date_created FROM item WHERE feed_id = :feed_id GROUP BY id ORDER BY id DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('feed_id', $feed['id']);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAssociative();

        if ($result) {
            //older than 96 hours, next collection in 12 hours
            if ($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 96)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 12));
                return $nextCollection->format('Y-m-d H:i:s');

            //older than 48 hours, next collection in 6 hours
            } elseif ($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 48)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 6));
                return $nextCollection->format('Y-m-d H:i:s');

            //older than 24 hours, next collection in 3 hours
            } elseif ($result['date_created'] < date('Y-m-d H:i:s', time() - 3600 * 24)) {
                $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 3));
                return $nextCollection->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    public function setItems(array $feed, $items): void
    {
        foreach ($items as $sp_item) {
            $link = $this->cleanLink($sp_item->get_link());
            $link = str_replace('http://www.lesnumeriques.com', 'https://www.lesnumeriques.com', $link);

            $sql = 'SELECT id FROM item WHERE link = :link';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('link', $link);
            $resultSet = $stmt->executeQuery();
            $result = $resultSet->fetchAssociative();

            if ($result) {
                break;
            }

            $insertItem = [];

            $insertItem['feed_id'] = $feed['id'];

            if ($sp_item->get_title()) {
                $insertItem['title'] = $this->cleanTitle($sp_item->get_title());
            } else {
                $insertItem['title'] = '-';
            }

            $insertItem['author_id'] = $this->setAuthorSimplePie($sp_item);

            $insertItem['link'] = $link;

            if ($content = $sp_item->get_content()) {
                if (class_exists('Tidy') && $content != '') {
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

                        $content = $this->cleanContent($tidy, 'store');
                    } catch (Exception $e) {
                    }
                } else {
                    $content = str_replace('<div', '<p', $content);
                    $content = str_replace('</div>', '</p>', $content);
                }

                if ($content != '') {
                    $insertItem['content']  = $content;
                } else {
                    $insertItem['content'] = '-';
                }
            } else {
                $insertItem['content'] = '-';
            }

            if ($sp_item->get_latitude() && $sp_item->get_longitude()) {
                $insertItem['latitude'] = $sp_item->get_latitude();
                $insertItem['longitude'] = $sp_item->get_longitude();
            }

            $dateReference = (new \Datetime())->format('Y-m-d H:i:s');

            if ($date = $sp_item->get_gmdate('Y-m-d H:i:s')) {
                if ($date > $dateReference) {
                    $insertItem['date'] = $dateReference;
                } else {
                    $insertItem['date'] = $date;
                }
            } else {
                $insertItem['date'] = $dateReference;
            }

            $insertItem['date_created'] = $dateReference;
            $insertItem['date_modified'] = $dateReference;

            $item_id = $this->insert('item', $insertItem);

            $this->setCategories($item_id, $sp_item->get_categories());

            $this->setEnclosures($item_id, $sp_item->get_enclosures());

            unset($sp_item);
        }
    }

    public function setAuthorSimplePie($sp_item): ?int
    {
        $author_id = null;

        if ($sp_author = $sp_item->get_author()) {
            $author_id = $this->setAuthor($sp_author->get_name());
            if ($sp_author->get_name()) {
                $author_id = $this->setAuthor($sp_author->get_name());
            }
        }

        return $author_id;
    }

    public function setAuthor(string $name): ?int
    {
        $author_id = null;

        if ($name != '') {
            $title = $this->cleanTitle($name);

            $cacheItem = $this->cacheDriver->getItem('readerself.author_title.'.(new AsciiSlugger())->slug($title));

            if ($cacheItem->isHit()) {
                $author_id = $cacheItem->get();
            } else {
                $sql = 'SELECT id FROM author WHERE title = :title';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('title', $title);
                $resultSet = $stmt->executeQuery();
                $result = $resultSet->fetchAssociative();

                if ($result) {
                    $author_id = $result['id'];
                } else {
                    $insertAuthor = [
                        'title' => $title,
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $author_id = $this->insert('author', $insertAuthor);
                }
                $cacheItem->set($author_id);
                $this->cacheDriver->save($cacheItem);
            }
        }

        return $author_id;
    }

    public function setCategories(int $item_id, $categories): void
    {
        if ($categories) {
            $titles = [];
            foreach ($categories as $sp_category) {
                if ($sp_category->get_label()) {
                    if (strstr($sp_category->get_label(), ',')) {
                        $categoriesPart = explode(',', $sp_category->get_label());
                        foreach ($categoriesPart as $title) {
                            $title = mb_strtolower($title, 'UTF-8');
                            $title = $this->cleanTitle($title);
                            if ($title != '') {
                                $titles[] = $title;
                            }
                        }
                    } else {
                        $title = mb_strtolower($sp_category->get_label(), 'UTF-8');
                        $title = $this->cleanTitle($title);
                        if ($title != '') {
                            $titles[] = $title;
                        }
                    }
                }
                unset($sp_category);
            }

            $titles = array_unique($titles);
            foreach ($titles as $title) {
                $category_id = $this->setCategory($title);

                $sql = 'SELECT id FROM item_category WHERE item_id = :item_id AND category_id = :category_id';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('item_id', $item_id);
                $stmt->bindValue('category_id', $category_id);
                $resultSet = $stmt->executeQuery();
                $result = $resultSet->fetchAssociative();

                if ($result) {
                } else {
                    $insertItemCategory = [
                        'item_id' => $item_id,
                        'category_id' => $category_id,
                    ];
                    $this->insert('item_category', $insertItemCategory);
                }
            }
            unset($titles);
        }
    }

    public function setEnclosures(int $item_id, $enclosures): void
    {
        if ($enclosures) {
            $links = [];
            foreach ($enclosures as $sp_enclosure) {
                if ($sp_enclosure->get_link() && $sp_enclosure->get_type()) {
                    $link = $this->cleanLink($sp_enclosure->get_link());

                    if (substr($link, -2) == '?#') {
                        $link = substr($link, 0, -2);
                    }

                    if (!in_array($link, $links)) {
                        $insertEnclosure = [
                            'item_id' => $item_id,
                            'link' => $link,
                            'type' => $sp_enclosure->get_type(),
                            'length' => is_numeric($sp_enclosure->get_length()) ? $sp_enclosure->get_length() : null,
                            'width' => is_numeric($sp_enclosure->get_width()) ? $sp_enclosure->get_width() : null,
                            'height' => is_numeric($sp_enclosure->get_height()) ? $sp_enclosure->get_height() : null,
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

    public function toAscii(string $url): string
    {
        $parse_url = parse_url($url);
        if (!isset($parse_url['host'])) {
            return $url;
        }
        if (mb_detect_encoding($parse_url['host']) != 'ASCII') {
            $url = str_replace($parse_url['host'], idn_to_ascii($parse_url['host']), $url);
        }
        return $url;
    }
}
