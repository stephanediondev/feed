<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Member;
use Symfony\Contracts\EventDispatcher\Event;

class MemberEvent extends Event
{
    private Member $member;

    public const CREATED = 'member.event.created';
    public const UPDATED = 'member.event.updated';
    public const DELETED = 'member.event.deleted';

    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
