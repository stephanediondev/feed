<?php

declare(strict_types=1);

namespace App\Model;

class PushModel
{
    private ?string $endpoint = null;

    private ?string $publicKey = null;

    private ?string $authenticationSecret = null;

    private ?string $contentEncoding = null;

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getAuthenticationSecret(): ?string
    {
        return $this->authenticationSecret;
    }

    public function setAuthenticationSecret(?string $authenticationSecret): self
    {
        $this->authenticationSecret = $authenticationSecret;

        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(?string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;

        return $this;
    }
}
