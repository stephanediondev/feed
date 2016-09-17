<?php
namespace Readerself\CoreBundle\Manager;

use Readerself\CoreBundle\Manager\AbstractManager;

use PDO;

class MigrationManager extends AbstractManager
{
    public function start()
    {

        //TODO test members exists


        //feed
        $sql = 'DELETE FROM feed';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT * FROM feeds';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while($result = $stmt->fetch()) {
            $insert = [
                'id' => $result['fed_id'],
                'title' => $result['fed_title'],
                'link' => $result['fed_link'],
                'website' => $result['fed_url'],
                'hostname' => $result['fed_host'],
                'description' => $result['fed_description'],
                'date_created' => $result['fed_datecreated'],
                'date_modified' => $result['fed_datecreated'],
            ];
            $this->insert('feed', $insert);
        }

        //subscription
        $sql = 'DELETE FROM subscription';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT * FROM subscriptions';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while($result = $stmt->fetch()) {
            $insert = [
                'id' => $result['sub_id'],
                'member_id' => $result['mbr_id'],
                'feed_id' => $result['fed_id'],
                'date_created' => $result['sub_datecreated'],
            ];
            $this->insert('subscription', $insert);
        }

        //author
        $sql = 'DELETE FROM author';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT * FROM authors';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while($result = $stmt->fetch()) {
            try {
                $insert = [
                    'id' => $result['auh_id'],
                    'title' => $result['auh_title'],
                    'date_created' => $result['auh_datecreated'],
                ];
                $this->insert('author', $insert);
            } catch(Exception $e) {
            }
        }

        //category
        $sql = 'DELETE FROM category';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        /*$sql = 'SELECT * FROM tags';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while($result = $stmt->fetch()) {
            try {
                $insert = [
                    'id' => $result['tag_id'],
                    'title' => $result['tag_title'],
                    'date_created' => $result['tag_datecreated'],
                ];
                $this->insert('category', $insert);
            } catch(Exception $e) {
            }
        }*/

        //item
        $sql = 'DELETE FROM item';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT * FROM items WHERE fed_id IN (SELECT id FROM feed) LIMIT 0,50000';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while($result = $stmt->fetch()) {
            try {
                $insert = [
                    'id' => $result['itm_id'],
                    'feed_id' => $result['fed_id'],
                    //'author_id' => $result['auh_id'],
                    'title' => $result['itm_title'],
                    'link' => $result['itm_link'],
                    'content' => $result['itm_content'],
                    'date' => $result['itm_date'],
                    'latitude' => $result['itm_latitude'],
                    'longitude' => $result['itm_longitude'],
                    'date_created' => $result['itm_datecreated'],
                    'date_modified' => $result['itm_datecreated'],
                ];
                $this->insert('item', $insert);
            } catch(Exception $e) {
            }
        }
    }
}
