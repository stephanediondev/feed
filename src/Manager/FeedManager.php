<?php

namespace App\Manager;

use App\Manager\AbstractManager;
use App\Entity\Feed;
use App\Event\FeedEvent;
use App\Repository\FeedRepository;

class FeedManager extends AbstractManager
{
    public FeedRepository $feedRepository;

    public $collectionFeedManager;

    private array $categories;

    private array $feeds;

    public function __construct(
        FeedRepository $feedRepository,
        CollectionFeedManager $collectionFeedManager
    ) {
        $this->feedRepository = $feedRepository;
        $this->collectionFeedManager = $collectionFeedManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->feedRepository->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->feedRepository->getList($parameters);
    }

    public function init()
    {
        return new Feed();
    }

    public function persist($data)
    {
        if ($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->feedRepository->persist($data);

        $event = new FeedEvent($data, $mode);
        $this->eventDispatcher->dispatch($event, 'Feed.after_persist');

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch($event, 'Feed.before_remove');

        $this->feedRepository->remove($data);

        $this->clearCache();
    }

    public function import($member, $opml)
    {
        $this->categories = [];
        $this->feeds = [];

        $this->transformOpml($opml);

        if (count($this->feeds) > 0) {
            $action_id = 3;

            foreach ($this->feeds as $obj) {
                $link = $this->cleanLink($obj->xmlUrl);

                $sql = 'SELECT id FROM feed WHERE link = :link';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('link', $link);
                $resultSet = $stmt->executeQuery();
                $test = $resultSet->fetchAssociative();

                if ($test) {
                    $feed_id = $test['id'];
                } else {
                    $parse_url = parse_url($obj->xmlUrl);

                    $insertFeed = [
                        'title' => $this->cleanTitle($obj->title),
                        'link' => $link,
                        'website' => $this->cleanWebsite($obj->htmlUrl),
                        'hostname' => $parse_url['host'],
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        'date_modified' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $feed_id = $this->insert('feed', $insertFeed);
                }

                $sql = 'SELECT id FROM action_feed WHERE feed_id = :feed_id AND member_id = :member_id AND action_id = :action_id';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('feed_id', $feed_id);
                $stmt->bindValue('member_id', $member->getId());
                $stmt->bindValue('action_id', $action_id);
                $resultSet = $stmt->executeQuery();
                $test = $resultSet->fetchAssociative();

                if ($test) {
                } else {
                    $insertActionFeed = [
                        'feed_id' => $feed_id,
                        'member_id' => $member->getId(),
                        'action_id' => $action_id,
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $this->insert('action_feed', $insertActionFeed);
                }
            }
        }
    }

    private function transformOpml($obj, $cat = false)
    {
        $feeds = array();
        if (isset($obj->outline) == 1) {
            foreach ($obj->outline as $outline) {
                if (isset($outline->outline) == 1) {
                    if ($outline->attributes()->title) {
                        $cat = strval($outline->attributes()->title);
                        $this->categories[] = $cat;
                    } elseif ($outline->attributes()->text) {
                        $cat = strval($outline->attributes()->text);
                        $this->categories[] = $cat;
                    }
                    $this->transformOpml($outline, $cat);
                } else {
                    $feed = new \stdClass();
                    foreach ($outline->attributes() as $k => $attribute) {
                        $feed->{$k} = strval($attribute);
                    }
                    $feed->flr = $cat;
                    $this->feeds[] = $feed;
                }
            }
        }
        return $feeds;
    }
}
