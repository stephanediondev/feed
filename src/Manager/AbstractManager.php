<?php

declare(strict_types=1);

namespace App\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractManager
{
    protected EntityManagerInterface $entityManager;

    protected Connection $connection;

    protected EventDispatcherInterface $eventDispatcher;

    protected RouterInterface $router;

    /**
     * @required
     */
    public function setRequired(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, RouterInterface $router): void
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public function clearCache(): void
    {
        if (function_exists('apcu_clear_cache')) {
            //apcu_clear_cache();
        }
    }

    public function count(string $table): int
    {
        $sql = 'SELECT COUNT(id) AS count FROM '.$table;
        $stmt = $this->connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAssociative();
        return $result['count'] ?? 0;
    }

    /**
     * @param array<mixed> $fields
     */
    public function insert(string $table, array $fields): int
    {
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($fields)).') VALUES ('.implode(', ', array_map(function ($n) {
            return ':'.$n;
        }, array_keys($fields))).')';
        $stmt = $this->connection->prepare($sql);
        foreach ($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->executeQuery();
        return intval($this->connection->lastInsertId());
    }

    /**
     * @param array<mixed> $fields
     */
    public function update(string $table, array $fields, int $id): void
    {
        $sql = 'UPDATE '.$table.' SET '.implode(', ', array_map(function ($n) {
            return $n.' = :'.$n;
        }, array_keys($fields))).' WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        foreach ($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('id', $id);
        $stmt->executeQuery();
    }

    public function setCategory(string $title): int
    {
        $sql = 'SELECT id FROM category WHERE title = :title';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('title', $title);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAssociative();

        if ($result) {
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
}
