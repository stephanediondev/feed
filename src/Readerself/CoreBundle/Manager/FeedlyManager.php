<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

class FeedlyManager extends AbstractManager
{
    public function start()
    {
        $content = json_decode(file_get_contents('http://s3.feedly.com/essentials/essentials_en-US.json'), true);

        foreach($content as $category => $sub) {
            foreach($sub['subscriptions'] as $result) {
                $category = mb_strtolower($sub['label'], 'UTF-8');
                $category = $this->cleanTitle($category);

                $result['title'] = $this->cleanTitle($result['title']);
                $result['id'] = $this->cleanLink(substr($result['id'], 5));
                if(substr($result['website'], 0, 4) != 'http') {
                    $result['website'] = 'http://'.$result['website'];
                }
                $result['website'] = $this->cleanWebsite($result['website']);

                $sql = 'SELECT id FROM feed WHERE link = :link';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('link', $result['id']);
                $stmt->execute();
                $test = $stmt->fetch();

                if($test) {
                    $feed_id = $test['id'];

                } else {
                    $parse_url = parse_url($result['website']);

                    $insertFeed = [
                        'title' => $result['title'],
                        'link' => $result['id'],
                        'website' => $result['website'],
                        'hostname' => $parse_url['host'],
                        'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                        'date_modified' => (new \Datetime())->format('Y-m-d H:i:s'),
                    ];
                    $feed_id = $this->insert('feed', $insertFeed);
                }

                $categories = [];
                if($category != '') {
                    $categories[] = $this->setCategory($category);
                }
                $categories[] = $this->setCategory('feedly');

                foreach($categories as $category_id) {
                    $sql = 'SELECT id FROM feed_category WHERE feed_id = :feed_id AND category_id = :category_id';
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue('feed_id', $feed_id);
                    $stmt->bindValue('category_id', $category_id);
                    $stmt->execute();
                    $test = $stmt->fetch();

                    if($test) {
                    } else {
                        $insertFeedCategory = [
                            'feed_id' => $feed_id,
                            'category_id' => $category_id,
                        ];
                        $this->insert('feed_category', $insertFeedCategory);
                    }
                }
            }
        }
    }

    public function setCategory($title)
    {
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

        return $category_id;
    }

    public function cleanWebsite($website)
    {
        $website = str_replace('&amp;', '&', $website);
        $website = mb_substr($website, 0, 255, 'UTF-8');

        return $website;
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
