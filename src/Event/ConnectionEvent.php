<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Connection;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionEvent extends Event
{
    private Connection $connection;

    public const CREATED = 'connection.event.created';
    public const UPDATED = 'connection.event.updated';
    public const DELETED = 'connection.event.deleted';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
