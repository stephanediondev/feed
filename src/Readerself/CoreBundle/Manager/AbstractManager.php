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
}
