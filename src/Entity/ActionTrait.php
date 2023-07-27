<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ActionTrait
{
    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "action_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Action $action = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private ?Member $member = null;

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'action' => $this->getAction() ? $this->getAction()->toArray() : null,
        ];
    }
}
