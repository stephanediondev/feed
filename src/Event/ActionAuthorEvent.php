<?php
declare(strict_types=1);

namespace App\Event;

use App\Entity\ActionAuthor;
use Symfony\Contracts\EventDispatcher\Event;

class ActionAuthorEvent extends Event
{
    private ActionAuthor $actionAuthor;

    public const CREATED = 'action_author.event.created';
    public const UPDATED = 'action_author.event.updated';
    public const DELETED = 'action_author.event.deleted';

    public function __construct(ActionAuthor $actionAuthor)
    {
        $this->actionAuthor = $actionAuthor;
    }

    public function getActionAuthor(): ActionAuthor
    {
        return $this->actionAuthor;
    }
}
