<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

class DiggManager extends AbstractManager
{
    public function start()
    {
        $content = json_decode(file_get_contents('https://digg.com/api/discovery/list.json'), true);

        foreach($content['data'] as $category => $sub) {
            foreach($sub['subs'] as $result) {
                $category = mb_strtolower($category, 'UTF-8');
                $category = $this->cleanTitle($category);

                $result['title'] = $this->cleanTitle($result['title']);
                $result['feed_url'] = $this->cleanLink($result['feed_url']);
                if(substr($result['html_url'], 0, 4) != 'http') {
                    $result['html_url'] = 'http://'.$result['html_url'];
                }
                $result['html_url'] = $this->cleanWebsite($result['html_url']);

                $sql = 'SELECT id FROM feed WHERE link = :link';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('link', $result['feed_url']);
                $stmt->execute();
                $test = $stmt->fetch();

                if($test) {
                    $feed_id = $test['id'];

                } else {
                    $parse_url = parse_url($result['html_url']);

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

                $categories = [];
                if($category != '') {
                    $categories[] = $this->setCategory($category);
                }
                $categories[] = $this->setCategory('digg');

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
}
