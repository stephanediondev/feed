<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected Connection $connection;

    /**
     * @psalm-return class-string
    */
    abstract public function getEntityClass(): string;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getEntityClass());

        $em = $this->getEntityManager();
        $this->connection = $em->getConnection();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param array<mixed> $fields
     */
    public function insert(string $table, array $fields): int
    {
        $this->connection->insert($table, $fields);
        return intval($this->connection->lastInsertId());
    }

    /**
     * @param array<mixed> $fields
     */
    public function update(string $table, array $fields, int $id): void
    {
        $this->connection->update($table, $fields, ['id' => $id]);
    }
}
