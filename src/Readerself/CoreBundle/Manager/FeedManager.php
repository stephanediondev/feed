<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Feed;
use Readerself\CoreBundle\Event\FeedEvent;

class FeedManager extends AbstractManager
{
    public $collectionFeedManager;

    public function __construct(
        CollectionFeedManager $collectionFeedManager
    ) {
        $this->collectionFeedManager = $collectionFeedManager;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Feed')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Feed')->getList($parameters);
    }

    public function init()
    {
        return new Feed();
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

        $event = new FeedEvent($data, $mode);
        $this->eventDispatcher->dispatch('Feed.after_persist', $event);

        $this->removeCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Feed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->removeCache();
    }

    public function directCreate($result)
    {
        $parse_url = parse_url($result['html_url']);

        //test feed
        $sql = 'SELECT id FROM feed WHERE link = :link';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('link', $result['feed_url']);
        $stmt->execute();
        $test = $stmt->fetch();

        if($test) {
            $feed_id = $test['id'];
        } else {
            $insertFeed = [
                'title' => $result['title'],
                'link' => $result['feed_url'],
                'website' => $result['html_url'],
                'hostname' => $parse_url['host'],
                'description' => $result['description'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                'date_modified' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];
            $feed_id = $this->insert('feed', $insertFeed);
        }

        $result['category'] = mb_strtolower($result['category'], 'UTF-8');

        $sql = 'SELECT id FROM category WHERE title = :title';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('title', $result['category']);
        $stmt->execute();
        $test = $stmt->fetch();

        if($test) {
            $category_id = $test['id'];
        } else {
            $insertCategory = [
                'title' => $result['category'],
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];
            $category_id = $this->insert('category', $insertCategory);
        }

        //TODO add test !!
        $insertFeedCategory = [
            'feed_id' => $feed_id,
            'category_id' => $category_id,
        ];
        $this->insert('feed_category', $insertFeedCategory);
    }
}
