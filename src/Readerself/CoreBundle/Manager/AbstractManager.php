<?php
namespace Readerself\CoreBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractManager
{
    protected $em;

    protected $connection;

    protected $eventDispatcher;

    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->connection = $em->getConnection();
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function clearCache()
    {
        if(function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }

    public function insert($table, $fields)
    {
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($fields)).') VALUES ('.implode(', ', array_map(function($n) {return ':'.$n;}, array_keys($fields))).')';
        $stmt = $this->connection->prepare($sql);
        foreach($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    public function update($table, $fields, $id)
    {
        $sql = 'UPDATE '.$table.' SET '.implode(', ', array_map(function($n) {return $n.' = :'.$n;}, array_keys($fields))).' WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        foreach($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('id', $id);
        $stmt->execute();
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
