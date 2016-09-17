<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use PDO;

class MigrationManager extends AbstractManager
{
    public function start()
    {

        //TODO test members exists

        $sql = 'SELECT fav.*, itm.*, auh.*, fed.* FROM favorites AS fav LEFT JOIN items AS itm ON itm.itm_id = fav.itm_id LEFT JOIN authors AS auh ON auh.auh_id = itm.auh_id LEFT JOIN feeds AS fed ON fed.fed_id = itm.fed_id WHERE fed.fed_id IS NOT NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach($results as $result) {
            //test item
            $sql = 'SELECT id FROM item WHERE link = :link';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('link', $result['itm_link']);
            $stmt->execute();
            $test = $stmt->fetch();

            if($test) {
                $item_id = $test['id'];
            } else {
                //test feed
                $sql = 'SELECT id FROM feed WHERE link = :link';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('link', $result['fed_link']);
                $stmt->execute();
                $test = $stmt->fetch();

                if($test) {
                    $feed_id = $test['id'];
                } else {
                    $insertFeed = [
                        'title' => $result['fed_title'],
                        'link' => $result['fed_link'],
                        'website' => $result['fed_url'],
                        'hostname' => $result['fed_host'],
                        'date_created' => $result['fed_datecreated'],
                        'date_modified' => $result['fed_datecreated'],
                    ];
                    $feed_id = $this->insert('feed', $insertFeed);
                }

                //test author
                if($result['auh_id']) {
                    $sql = 'SELECT id FROM author WHERE title = :title';
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue('title', $result['auh_title']);
                    $stmt->execute();
                    $test = $stmt->fetch();

                    if($test) {
                        $author_id = $test['id'];
                    } else {
                        $insertAuthor = [
                            'title' => $result['auh_title'],
                            'date_created' => $result['auh_datecreated'],
                        ];
                        $author_id = $this->insert('author', $insertAuthor);
                    }
                } else {
                    $author_id = null;
                }

                $insertItem = [
                    'feed_id' => $feed_id,
                    'author_id' => $author_id,
                    'title' => $result['itm_title'],
                    'link' => $result['itm_link'],
                    'content' => $result['itm_content'],
                    'date' => $result['itm_date'],
                    'latitude' => $result['itm_latitude'],
                    'longitude' => $result['itm_longitude'],
                    'date_created' => $result['itm_datecreated'],
                    'date_modified' => $result['itm_datecreated'],
                ];
                $item_id = $this->insert('item', $insertItem);
            }

            //test star
            $sql = 'SELECT id FROM action_item_member WHERE member_id = :member_id AND item_id = :item_id AND action_id = :action_id';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('member_id', $result['mbr_id']);
            $stmt->bindValue('item_id', $item_id);
            $stmt->bindValue('action_id', 2);
            $stmt->execute();
            $test = $stmt->fetch();

            if($test) {
            } else {
                $insertActionItemMember = [
                    'member_id' => $result['mbr_id'],
                    'item_id' => $item_id,
                    'action_id' => 2,
                    'date_created' => $result['fav_datecreated'],
                ];
                $this->insert('action_item_member', $insertActionItemMember);
            }
        }
    }
}
