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

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new FeedEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Feed.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }

    public function import($member, $opml)
    {
        $this->categories = [];
        $this->feeds = [];

        $this->transformOpml($opml);

        if(count($this->feeds) > 0) {

            $action_id = 3;

            foreach($this->feeds as $obj) {
                $link = $this->cleanLink($obj->xmlUrl);

                $sql = 'SELECT id FROM feed WHERE link = :link';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('link', $link);
                $stmt->execute();
                $test = $stmt->fetch();

                if($test) {
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

                $sql = 'SELECT id FROM action_feed_member WHERE feed_id = :feed_id AND member_id = :member_id AND action_id = :action_id';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('feed_id', $feed_id);
                $stmt->bindValue('member_id', $member->getId());
                $stmt->bindValue('action_id', $action_id);
                $stmt->execute();
                $test = $stmt->fetch();

                if($test) {
                } else {
                    $insertActionFeedMember = [
                        'feed_id' => $feed_id,
                        'member_id' => $member->getId(),
                        'action_id' => $action_id,
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $this->insert('action_feed_member', $insertActionFeedMember);
                }
            }
        }
    }

    private function transformOpml($obj, $cat = false) {
        $feeds = array();
        if(isset($obj->outline) == 1) {
            foreach($obj->outline as $outline) {
                if(isset($outline->outline) == 1) {
                    if($outline->attributes()->title) {
                        $cat = strval($outline->attributes()->title);
                        $this->categories[] = $cat;
                    } else if($outline->attributes()->text) {
                        $cat = strval($outline->attributes()->text);
                        $this->categories[] = $cat;
                    }
                    $this->transformOpml($outline, $cat);
                } else {
                    $feed = new \stdClass();
                    foreach($outline->attributes() as $k => $attribute) {
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
