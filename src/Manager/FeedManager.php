<?php

namespace App\Manager;

use App\Entity\Feed;
use App\Entity\Member;
use App\Event\FeedEvent;
use App\Manager\AbstractManager;
use App\Repository\FeedRepository;
use SimpleXMLElement;

class FeedManager extends AbstractManager
{
    private FeedRepository $feedRepository;

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

    public function getOne($parameters = []): ?Feed
    {
        return $this->feedRepository->getOne($parameters);
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
            $eventName = FeedEvent::CREATED;
            $data->setDateCreated(new \Datetime());
        } else {
            $eventName = FeedEvent::UPDATED;
        }
        $data->setDateModified(new \Datetime());

        $this->feedRepository->persist($data);

        $event = new FeedEvent($data);
        $this->eventDispatcher->dispatch($event, $eventName);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data);
        $this->eventDispatcher->dispatch($event, FeedEvent::DELETED);

        $this->feedRepository->remove($data);

        $this->clearCache();
    }

    public function import(Member $member, $opml)
    {
        $data = $this->transformOpml($opml);

        if (0 < count($data['feeds'])) {
            $action_id = 3;

            foreach ($data['feeds'] as $obj) {
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

    private function transformOpml(SimpleXMLElement $obj, bool $cat = false): array
    {
        $data = [
            'feeds' => [],
            'categories' => [],
        ];
        if (isset($obj->outline) == 1) {
            foreach ($obj->outline as $outline) {
                if (isset($outline->outline) == 1) {
                    if ($outline->attributes()->title) {
                        $cat = strval($outline->attributes()->title);
                        $data['categories'][] = $cat;
                    } elseif ($outline->attributes()->text) {
                        $cat = strval($outline->attributes()->text);
                        $data['categories'][] = $cat;
                    }
                    $data = array_merge($data, $this->transformOpml($outline, $cat));
                } else {
                    $feed = new \stdClass();
                    foreach ($outline->attributes() as $k => $attribute) {
                        $feed->{$k} = strval($attribute);
                    }
                    $feed->flr = $cat;
                    $data['feeds'][] = $feed;
                }
            }
        }
        return $data;
    }
}
