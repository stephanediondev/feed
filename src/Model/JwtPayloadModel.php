<?php
declare(strict_types=1);

namespace App\Model;

/*
    https://www.rfc-editor.org/rfc/rfc7519#section-4
    4.  JWT Claims
    4.1.  Registered Claim Names
    4.1.1.  "iss" (Issuer) Claim
    4.1.2.  "sub" (Subject) Claim
    4.1.3.  "aud" (Audience) Claim
    4.1.4.  "exp" (Expiration Time) Claim
    4.1.5.  "nbf" (Not Before) Claim
    4.1.6.  "iat" (Issued At) Claim
    4.1.7.  "jti" (JWT ID) Claim
*/
class JwtPayloadModel
{
    private ?string $jwtId = null;

    public function getJwtId(): ?string
    {
        return $this->jwtId;
    }

    public function setJwtId(?string $jwtId): self
    {
        $this->jwtId = $jwtId;
        return $this;
    }

    private ?string $issuer = null;

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }

    private ?string $audience = null;

    public function getAudience(): ?string
    {
        return $this->audience;
    }

    public function setAudience(?string $audience): self
    {
        $this->audience = $audience;
        return $this;
    }

    private ?string $subject = null;

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    private ?int $expirationTime = null;

    public function getExpirationTime(): ?int
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?int $expirationTime): self
    {
        $this->expirationTime = $expirationTime;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->getJwtId()) {
            $payload['jit'] = $this->getJwtId();
        }

        if ($this->getIssuer()) {
            $payload['iss'] = $this->getIssuer();
        }

        if ($this->getAudience()) {
            $payload['aud'] = $this->getAudience();
        }

        if ($this->getSubject()) {
            $payload['sub'] = $this->getSubject();
        }

        if ($this->getExpirationTime()) {
            $payload['exp'] = $this->getExpirationTime();
        }

        return $payload;
    }

    public function validAudienceAndSubject(string $audience, string $subject): bool
    {
        if (null === $this->getAudience() || $audience !== $this->getAudience()) {
            return false;
        }

        if (null === $this->getSubject() || $subject !== $this->getSubject()) {
            return false;
        }

        return true;
    }
}
