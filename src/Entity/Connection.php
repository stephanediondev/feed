<?php
declare(strict_types=1);

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
    private ?String $type = null;

    #[ORM\Column(name: "token", type: "string", length: 255, nullable: false)]
    private ?String $token = null;

    #[ORM\Column(name: "ip", type: "string", length: 255, nullable: true)]
    private ?String $ip = null;

    #[ORM\Column(name: "agent", type: "string", length: 255, nullable: true)]
    private ?String $agent = null;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Member $member = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(?string $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

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
            'id' => $this->getId(),
            'member' => $this->getMember()->toArray(),
            'type' => $this->getType(),
            'token' => $this->getToken(),
            'agent' => $this->getAgent(),
            'ip' => $this->getIp(),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            'date_modified' => $this->getDateModified()->format('Y-m-d H:i:s'),
        ];
    }
}
