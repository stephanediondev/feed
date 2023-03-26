<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: "member")]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(name: "id", type: "integer", options: ["unsigned" => true]), ORM\Id, ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "email", type: "string", length: 255, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(name: "password", type: "string", length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: "administrator", type: "boolean", nullable: false, options: ["default" => 0])]
    private bool $administrator = false;

    #[ORM\Column(name: "date_created", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(name: "date_modified", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $dateModified = null;

    private ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSalt(): null
    {
        return null;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->getAdministrator()) {
            $roles[] = 'ROLE_ADMINISTRATOR';
        } else {
            $roles[] = 'ROLE_USER';
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
        ];
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail() ?? '';
    }
}
