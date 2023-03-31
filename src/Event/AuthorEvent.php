<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Author;
use Symfony\Contracts\EventDispatcher\Event;

class AuthorEvent extends Event
{
    private Author $author;

    public const CREATED = 'author.event.created';
    public const UPDATED = 'author.event.updated';
    public const DELETED = 'author.event.deleted';

    public function __construct(Author $author)
    {
        $this->author = $author;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }
}
