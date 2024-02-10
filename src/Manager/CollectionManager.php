<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Collection;
use App\Event\CollectionEvent;
use App\Helper\CleanHelper;
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

    protected ApcuAdapter $cacheDriver;

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

    public function persist(Collection $collection): void
    {
        if ($collection->getId() === null) {
            $eventName = CollectionEvent::CREATED;
        } else {
            $eventName = CollectionEvent::UPDATED;
        }
        $collection->setDateModified(new \Datetime());

        $this->collectionRepository->persist($collection);

        $event = new CollectionEvent($collection);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();
    }

    public function remove(Collection $collection): void
    {
        $event = new CollectionEvent($collection);
        $this->eventDispatcher->dispatch($event, CollectionEvent::DELETED);

        $this->collectionRepository->remove($collection);

        $this->clearCache();
    }

    public function start(?int $feed_id = null): void
    {
        $startTime = microtime(true);

        $feeds = 0;
        $errors = 0;

        if ($feed_id) {
            $sql = 'SELECT id, link FROM feed WHERE id = :feed_id';
            $stmt = $this->collectionRepository->getConnection()->prepare($sql);
            $stmt->bindValue('feed_id', $feed_id);
        } else {
            $sql = 'SELECT id, link FROM feed WHERE next_collection IS NULL OR next_collection <= :date';
            $stmt = $this->collectionRepository->getConnection()->prepare($sql);
            $stmt->bindValue('date', (new \Datetime())->format('Y-m-d H:i:s'));
        }
        $resultSet = $stmt->executeQuery();
        $feeds_result = $resultSet->fetchAllAssociative();

        $collection = new Collection();
        $collection->setFeeds(count($feeds_result));
        $this->persist($collection);

        foreach ($feeds_result as $feed) {
            $feeds++;

            $insertCollectionFeed = [
                'collection_id' => $collection->getId(),
                'feed_id' => $feed['id'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];

            $parseUrl = parse_url($feed['link']);

            if (false === isset($parseUrl['scheme']) || ($parseUrl['scheme'] != 'http' && $parseUrl['scheme'] != 'https')) {
                $errors++;
                $insertCollectionFeed['error'] = 'Unvalid scheme';
            } else {
                try {
                    $simplepieFeed = new SimplePie();
                    if (true === isset($simplepieFeed->sanitize->strip_htmltags)) {
                        $stripTagsNew = [];
                        foreach ($simplepieFeed->sanitize->strip_htmltags as $tag) {
                            if ($tag != 'iframe') {
                                $stripTagsNew[] = $tag;
                            }
                        }
                        $simplepieFeed->sanitize->strip_htmltags = $stripTagsNew;
                    }
                    $simplepieFeed->set_feed_url($this->toAscii($feed['link']));
                    $simplepieFeed->enable_cache(false);
                    $simplepieFeed->set_timeout(15);
                    $simplepieFeed->force_feed(true);
                    $simplepieFeed->init();
                    $simplepieFeed->handle_content_type();
                    $simplepieFeed->set_curl_options([
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_SSL_VERIFYPEER => false
                    ]);

                    if ($simplepieFeed->error()) {
                        //next collection in 12 hours
                        $nextCollection = new \DateTime(date('Y-m-d H:i:s', time() + 3600 * 12));
                        $updateFeed = [];
                        $updateFeed['next_collection'] = $nextCollection->format('Y-m-d H:i:s');
                        $this->collectionRepository->update('feed', $updateFeed, $feed['id']);

                        $errors++;
                        $insertCollectionFeed['error'] = $simplepieFeed->error();
                    }

                    if (!$simplepieFeed->error()) {
                        if ($items = $simplepieFeed->get_items()) {
                            $this->setItems($feed, $items);
                        }

                        if ($website = $simplepieFeed->get_link()) {
                            $parseUrl = parse_url($website);

                            $updateFeed = [];
                            $updateFeed['title'] = $simplepieFeed->get_title() ? CleanHelper::cleanTitle($simplepieFeed->get_title()) : '-';
                            $updateFeed['website'] = CleanHelper::cleanWebsite($website);
                            $updateFeed['link'] = $simplepieFeed->subscribe_url() ? CleanHelper::cleanLink($simplepieFeed->subscribe_url()) : null;
                            $updateFeed['hostname'] = isset($parseUrl['host']) ? $parseUrl['host'] : null;
                            $updateFeed['description'] = $simplepieFeed->get_description();

                            $updateFeed['language'] = $simplepieFeed->get_language() ? substr($simplepieFeed->get_language(), 0, 2) : null;

                            $updateFeed['next_collection'] = $this->setNextCollection($feed);

                            $updateFeed['date_modified'] = (new \Datetime())->format('Y-m-d H:i:s');

                            $this->collectionRepository->update('feed', $updateFeed, $feed['id']);
                        }
                    }
                    $simplepieFeed->__destruct();
                    unset($simplepieFeed);
                } catch (Exception $e) {
                    $errors++;
                    $insertCollectionFeed['error'] = $e->getMessage();
                }
            }

            $this->collectionRepository->insert('collection_feed', $insertCollectionFeed);
        }

        $collection->setFeeds($feeds);
        $collection->setErrors($errors);
        $collection->setTime(microtime(true) - $startTime);
        $collection->setMemory(memory_get_peak_usage());
        $this->persist($collection);

        $sql = 'SELECT id FROM member';
        $stmt = $this->collectionRepository->getConnection()->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $members_result = $resultSet->fetchAllAssociative();

        foreach ($members_result as $member) {
            $this->memberManager->syncUnread($member['id']);
        }
    }

    /**
     * @param array<mixed> $feed
     */
    public function setNextCollection(array $feed): ?string
    {
        $sql = 'SELECT date_created FROM item WHERE feed_id = :feed_id GROUP BY id ORDER BY id DESC';
        $stmt = $this->collectionRepository->getConnection()->prepare($sql);
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

    /**
     * @param array<mixed> $feed
     * @param array<\SimplePie\Item> $items
     */
    public function setItems(array $feed, array $items): void
    {
        foreach ($items as $simplepieItem) {
            if ($link = $simplepieItem->get_link()) {
                $link = CleanHelper::cleanLink($link);

                $sql = 'SELECT id FROM item WHERE link = :link';
                $stmt = $this->collectionRepository->getConnection()->prepare($sql);
                $stmt->bindValue('link', $link);
                $resultSet = $stmt->executeQuery();
                $result = $resultSet->fetchAssociative();

                if ($result) {
                    break;
                }

                $insertItem = [];

                $insertItem['feed_id'] = $feed['id'];

                if ($simplepieItem->get_title()) {
                    $insertItem['title'] = CleanHelper::cleanTitle($simplepieItem->get_title());
                } else {
                    $insertItem['title'] = '-';
                }

                $insertItem['author_id'] = $this->setAuthorSimplePie($simplepieItem);

                $insertItem['link'] = $link;

                if ($content = $simplepieItem->get_content()) {
                    if (class_exists('Tidy')) {
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

                            $content = CleanHelper::cleanContent(tidy_get_output($tidy), 'store');
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

                if ($simplepieItem->get_latitude() && $simplepieItem->get_longitude()) {
                    $insertItem['latitude'] = $simplepieItem->get_latitude();
                    $insertItem['longitude'] = $simplepieItem->get_longitude();
                }

                $dateReference = (new \Datetime())->format('Y-m-d H:i:s');

                if ($date = $simplepieItem->get_gmdate('Y-m-d H:i:s')) {
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

                $item_id = $this->collectionRepository->insert('item', $insertItem);

                if ($categories = $simplepieItem->get_categories()) {
                    $this->setCategories($item_id, $categories);
                }

                if ($enclosures = $simplepieItem->get_enclosures()) {
                    $this->setEnclosures($item_id, $enclosures);
                }

                unset($simplepieItem);
            }
        }
    }

    public function setAuthorSimplePie(\SimplePie\Item $simplepieItem): ?int
    {
        $author_id = null;

        if ($simplepieAuthor = $simplepieItem->get_author()) {
            if ($simplepieAuthor->get_name()) {
                $author_id = $this->setAuthor($simplepieAuthor->get_name());
            }
        }

        return $author_id;
    }

    public function setAuthor(string $name): ?int
    {
        $author_id = null;

        if ($name != '') {
            $title = CleanHelper::cleanTitle($name);

            $cacheItem = $this->cacheDriver->getItem('readerself.author_title.'.(new AsciiSlugger())->slug($title));

            if ($cacheItem->isHit()) {
                $author_id = $cacheItem->get();
            } else {
                $sql = 'SELECT id FROM author WHERE title = :title';
                $stmt = $this->collectionRepository->getConnection()->prepare($sql);
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
                    $author_id = $this->collectionRepository->insert('author', $insertAuthor);
                }
                $cacheItem->set($author_id);
                $this->cacheDriver->save($cacheItem);
            }
        }

        return $author_id;
    }

    /**
     * @param array<\SimplePie\Category> $categories
     */
    public function setCategories(int $item_id, array $categories): void
    {
        if ($categories) {
            $titles = [];
            foreach ($categories as $simplepieCategory) {
                if ($simplepieCategory->get_label()) {
                    if (strstr($simplepieCategory->get_label(), ',')) {
                        $categoriesPart = explode(',', $simplepieCategory->get_label());
                        foreach ($categoriesPart as $title) {
                            $title = mb_strtolower($title, 'UTF-8');
                            $title = CleanHelper::cleanTitle($title);
                            if ($title != '') {
                                $titles[] = $title;
                            }
                        }
                    } else {
                        $title = mb_strtolower($simplepieCategory->get_label(), 'UTF-8');
                        $title = CleanHelper::cleanTitle($title);
                        if ($title != '') {
                            $titles[] = $title;
                        }
                    }
                }
                unset($simplepieCategory);
            }

            $titles = array_unique($titles);
            foreach ($titles as $title) {
                $sql = 'SELECT id FROM category WHERE title = :title';
                $stmt = $this->collectionRepository->getConnection()->prepare($sql);
                $stmt->bindValue('title', $title);
                $resultSet = $stmt->executeQuery();
                $result = $resultSet->fetchAssociative();

                if ($result) {
                    $category_id = $result['id'];
                } else {
                    $insertCategory = [
                        'title' => $title,
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $category_id = $this->collectionRepository->insert('category', $insertCategory);
                }

                $sql = 'SELECT id FROM item_category WHERE item_id = :item_id AND category_id = :category_id';
                $stmt = $this->collectionRepository->getConnection()->prepare($sql);
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
                    $this->collectionRepository->insert('item_category', $insertItemCategory);
                }
            }
            unset($titles);
        }
    }

    /**
     * @param array<\SimplePie\Enclosure> $enclosures
     */
    public function setEnclosures(int $item_id, array $enclosures): void
    {
        if ($enclosures) {
            $links = [];
            foreach ($enclosures as $simplepieEnclosure) {
                if ($simplepieEnclosure->get_link() && $simplepieEnclosure->get_type()) {
                    $link = CleanHelper::cleanLink($simplepieEnclosure->get_link());

                    if (substr($link, -2) == '?#') {
                        $link = substr($link, 0, -2);
                    }

                    if (!in_array($link, $links)) {
                        $insertEnclosure = [
                            'item_id' => $item_id,
                            'link' => $link,
                            'type' => $simplepieEnclosure->get_type(),
                            'length' => $simplepieEnclosure->get_length() ? $simplepieEnclosure->get_length() : null,
                            'width' => $simplepieEnclosure->get_width() && is_numeric($simplepieEnclosure->get_width()) ? $simplepieEnclosure->get_width() : null,
                            'height' => $simplepieEnclosure->get_height() && is_numeric($simplepieEnclosure->get_height()) ? $simplepieEnclosure->get_height() : null,
                            'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        ];
                        $this->collectionRepository->insert('enclosure', $insertEnclosure);

                        $links[] = $link;
                    }
                }
                unset($simplepieEnclosure);
            }
            unset($links);
        }
    }

    public function toAscii(string $url): string
    {
        $parseUrl = parse_url($url);
        if (!isset($parseUrl['host'])) {
            return $url;
        }
        if (mb_detect_encoding($parseUrl['host']) != 'ASCII') {
            if ($idn = idn_to_ascii($parseUrl['host'])) {
                $url = str_replace($parseUrl['host'], $idn, $url);
            }
        }
        return $url;
    }
}
