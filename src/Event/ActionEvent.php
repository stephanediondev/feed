<?php

namespace App\Event;

use App\Entity\Action;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    private Action $action;

    public const CREATED = 'action.event.created';
    public const UPDATED = 'action.event.updated';
    public const DELETED = 'action.event.deleted';

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
}
