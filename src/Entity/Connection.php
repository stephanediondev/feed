<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\DateCreatedTrait;
use App\Entity\ExtraFieldsTrait;
use App\Entity\IdTrait;
use App\Repository\ConnectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConnectionRepository::class)]
#[ORM\Table(name: "connection")]
#[ORM\UniqueConstraint(name: "type_token", columns: ["type", "token"])]
#[ORM\Index(name: "member_id", columns: ["member_id"])]
class Connection
{
    use ExtraFieldsTrait;
    use IdTrait;
    use DateCreatedTrait;

    public const TYPE_LOGIN = 'login';
    public const TYPE_PUSH = 'push';
    public const TYPE_PINBOARD = 'pinboard';

    #[ORM\Column(name: "type", type: "string", length: 255, nullable: false)]
    private ?String $type = null;

    #[ORM\Column(name: "token", type: "string", length: 500, nullable: false)]
    private ?String $token = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Member", inversedBy: "", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", onDelete: "cascade", nullable: false)]
    private ?Member $member = null;

    public function __construct()
    {
        $this->dateCreated = new \Datetime();
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

    public function getIp(): ?string
    {
        return $this->getExtraField('ip');
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $address = [];
        if ($this->getExtraField('city')) {
            $address[] = $this->getExtraField('city');
        }
        if ($this->getExtraField('subdivision')) {
            $address[] = $this->getExtraField('subdivision');
        }
        if ($this->getExtraField('country')) {
            $address[] = $this->getExtraField('country');
        }

        return [
            'id' => $this->getId(),
            'member' => $this->getMember() ? $this->getMember()->toArray() : null,
            'type' => $this->getType(),
            'token' => $this->getToken(),
            'ip' => $this->getExtraField('ip'),
            'hostname' => $this->getExtraField('hostname'),
            'client' => $this->getExtraField('client'),
            'os' => $this->getExtraField('os'),
            'device' => $this->getExtraField('device'),
            'brand' => $this->getExtraField('brand'),
            'model' => $this->getExtraField('model'),
            'address' => 0 < count($address) ? implode(', ', $address) : null,
            'latitude' => $this->getExtraField('latitude'),
            'longitude' => $this->getExtraField('longitude'),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : null,
        ];
    }
}
