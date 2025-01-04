<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\DateCreatedTrait;
use App\Entity\IdTrait;
use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: "member")]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdTrait;
    use DateCreatedTrait;

    #[ORM\Column(name: "email", type: "string", length: 255, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(name: "password", type: "string", length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: "administrator", type: "boolean", nullable: false, options: ["default" => 0])]
    private bool $administrator = false;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    private ?string $plainPassword = null;

    #[ORM\Column(name: "passkey_challenge", type: "string", length: 255, nullable: true)]
    private ?string $passkeyChallenge = null;

    public function __construct()
    {
        $this->passkeys = new ArrayCollection();
        $this->dateCreated = new \Datetime();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAdministrator(): bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): self
    {
        $this->administrator = $administrator;

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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        $roles[] = 'ROLE_USER';
        if ($this->getAdministrator()) {
            $roles[] = 'ROLE_ADMIN';
        }
        return $roles;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'administrator' => $this->getAdministrator(),
            'date_created' => $this->getDateCreated() ? $this->getDateCreated()->format('Y-m-d H:i:s') : null,
            'date_modified' => $this->getDateModified() ? $this->getDateModified()->format('Y-m-d H:i:s') : null,
        ];
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail() ?? '';
    }

    public function getPasskeyChallenge(): ?string
    {
        if (null !== $this->passkeyChallenge) {
            return base64_decode($this->passkeyChallenge);
        }

        return null;
    }

    public function setPasskeyChallenge(?string $passkeyChallenge): self
    {
        if (null !== $passkeyChallenge) {
            $this->passkeyChallenge = base64_encode($passkeyChallenge);
        } else {
            $this->passkeyChallenge = null;
        }

        return $this;
    }

    /** @var Collection<int, MemberPasskey> $passkeys */
    #[ORM\OneToMany(targetEntity: "App\Entity\MemberPasskey", mappedBy: "member", fetch: "LAZY", cascade: ["persist"], orphanRemoval: true)]
    private Collection $passkeys;
    /**
     * @return Collection<int, MemberPasskey>
     */
    public function getPasskeys(): Collection
    {
        return $this->passkeys;
    }
    public function addPasskey(MemberPasskey $memberPasskey): self
    {
        if (false === $this->hasPasskey($memberPasskey)) {
            $this->passkeys->add($memberPasskey);
            $memberPasskey->setMember($this);
        }
        return $this;
    }
    public function removePasskey(MemberPasskey $memberPasskey): self
    {
        if (true === $this->hasPasskey($memberPasskey)) {
            $this->passkeys->removeElement($memberPasskey);
            $memberPasskey->setMember(null);
        }
        return $this;
    }
    public function hasPasskey(MemberPasskey $memberPasskey): bool
    {
        return $this->passkeys->contains($memberPasskey);
    }
}
