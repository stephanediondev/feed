<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\ActionTrait;
use App\Entity\DateCreatedTrait;
use App\Entity\IdTrait;
use App\Repository\ActionFeedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionFeedRepository::class)]
#[ORM\Table(name: "action_feed")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "feed_id", columns: ["feed_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionFeed
{
    use ActionTrait;
    use IdTrait;
    use DateCreatedTrait;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Feed", inversedBy: "actions", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "feed_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Feed $feed = null;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
    }

    public function getFeed(): ?Feed
    {
        return $this->feed;
    }

    public function setFeed(?Feed $feed): self
    {
        $this->feed = $feed;

        return $this;
    }
}
