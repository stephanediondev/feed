<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

class AolManager extends AbstractManager
{
    public function start($rootDir)
    {
        $content = json_decode(file_get_contents($rootDir.'/../src/Readerself/CoreBundle/DataFixtures/aol-reader-discover.json'), true);

        foreach($content['discover'] as $result) {
            $result['title'] = $this->cleanTitle($result['title']);
            $result['feed_url'] = $this->cleanLink($result['url']);

            $sql = 'SELECT id FROM feed WHERE link = :link';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('link', $result['url']);
            $stmt->execute();
            $test = $stmt->fetch();

            if($test) {
                $feed_id = $test['id'];

            } else {
                $insertFeed = [
                    'title' => $result['title'],
                    'link' => $result['url'],
                    'website' => $result['url'],
                    'hostname' => $result['domain'],
                    'description' => isset($result['description']) ? $result['description'] : '',
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                    'date_modified' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $feed_id = $this->insert('feed', $insertFeed);
            }

            $category = mb_strtolower($result['category'], 'UTF-8');
            $category = $this->cleanTitle($category);

            $categories = [];
            if($category != '') {
                $categories[] = $this->setCategory($category);
            }
            $categories[] = $this->setCategory('aol');

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
