<?php

namespace App\Entity;

use App\Repository\ConnectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConnectionRepository::class)]
#[ORM\Table(name: "connection")]
#[ORM\UniqueConstraint(name: "type_token", columns: ["type", "token"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class Connection
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "type", type: "string", length: 255, nullable: false)]
    private $type;

    #[ORM\Column(name: "token", type: "string", length: 255, nullable: false)]
    private $token;

    #[ORM\Column(name: "ip", type: "string", length: 255, nullable: true)]
    private $ip;

    #[ORM\Column(name: "agent", type: "string", length: 255, nullable: true)]
    private $agent;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
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
     * Set type
     *
     * @param string $type
     *
     * @return Connection
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set token
     *
     * @param string $token
     *
     * @return Connection
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Connection
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set agent
     *
     * @param string $agent
     *
     * @return Connection
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * Get agent
     *
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTimeInterface $dateCreated
     *
     * @return Connection
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
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return Connection
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTimeInterface
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set member
     *
     * @param \App\Entity\Member $member
     *
     * @return Connection
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMember()->toArray(),
            'type' => $this->getType(),
            'token' => $this->getToken(),
            'agent' => $this->getAgent(),
            'ip' => $this->getIp(),
            'date_created' => $this->getDateCreated()->format('Y-m-d H:i:s'),
            'date_modified' => $this->getDateModified()->format('Y-m-d H:i:s'),
        ];
    }
}
