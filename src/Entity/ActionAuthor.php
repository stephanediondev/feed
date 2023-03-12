<?php

namespace App\Entity;

use App\Repository\ActionAuthorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionAuthorRepository::class)]
#[ORM\Table(name: "action_author")]
#[ORM\Index(name: "action_id", columns: ["action_id"])]
#[ORM\Index(name: "author_id", columns: ["author_id"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class ActionAuthor
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Author", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $author;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Action", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "action_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private $action;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: true)]
    private $member;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return ActionAuthor
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTimeInterface
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set author
     *
     * @param \App\Entity\Author $author
     *
     * @return ActionAuthor
     */
    public function setAuthor(Author $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \App\Entity\Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set action
     *
     * @param \App\Entity\Action $action
     *
     * @return ActionAuthor
     */
    public function setAction(Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \App\Entity\Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set member
     *
     * @param \App\Entity\Member $member
     *
     * @return ActionAuthor
     */
    public function setMember(Member $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \App\Entity\Member
     */
    public function getMember()
    {
        return $this->member;
    }
}
