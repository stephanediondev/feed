<?php

namespace App\Entity;

use App\Entity\DateCreatedTrait;
use App\Entity\IdTrait;
use App\Repository\MemberPasskeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberPasskeyRepository::class)]
#[ORM\Table(name: "member_passkey")]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
#[ORM\Index(name: "credential_id", columns: ["credential_id"])]
#[ORM\Index(name: "date_created", columns: ["date_created"])]
class MemberPasskey
{
    use IdTrait;
    use DateCreatedTrait;

    public const ALIAS = 'mbr_psk';
    public const DEFAULT_SORT_FIELD = 'mbr_psk.id';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
    }

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "passkeys", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Member $member = null;
    public function getMember(): ?Member
    {
        return $this->member;
    }
    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private ?string $title = null;
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    #[ORM\Column(name: "credential_id", type: "string", length: 255, nullable: false)]
    private ?string $credentialId = null;
    public function getCredentialId(): ?string
    {
        return $this->credentialId;
    }
    public function setCredentialId(?string $credentialId): self
    {
        $this->credentialId = $credentialId;
        return $this;
    }

    #[ORM\Column(name: "public_key", type: "text", length: 65535, nullable: false)]
    private ?string $publicKey = null;
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }
    public function setPublicKey(?string $publicKey): self
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    #[ORM\Column(name: "last_time_active", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $lastTimeActive = null;
    public function getLastTimeActive(): ?\DateTimeInterface
    {
        return $this->lastTimeActive;
    }
    public function setLastTimeActive(?\DateTimeInterface $lastTimeActive): self
    {
        $this->lastTimeActive = $lastTimeActive;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMember() ? $this->getMember()->toArray() : null,
            'title' => $this->getTitle(),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
        ];
    }
}
