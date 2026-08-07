<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class JwtClaims
{
    /**
     * @param list<string> $audiences
     * @param array<string, scalar|null> $additional
     */
    public function __construct(
        private string $subject,
        private ?string $issuer,
        private array $audiences,
        private ?DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $notBefore,
        private ?string $scope,
        private array $additional = []
    ) {
        if ($this->subject === '' || strlen($this->subject) > 512) {
            throw new InvalidArgumentException('JWT subject is invalid.');
        }

        foreach ($this->audiences as $audience) {
            if ($audience === '' || strlen($audience) > 512) {
                throw new InvalidArgumentException('JWT audience is invalid.');
            }
        }
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function issuer(): ?string
    {
        return $this->issuer;
    }

    /** @return list<string> */
    public function audiences(): array
    {
        return $this->audiences;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function notBefore(): ?DateTimeImmutable
    {
        return $this->notBefore;
    }

    public function scope(): ?string
    {
        return $this->scope;
    }

    /** @return array<string, scalar|null> */
    public function additional(): array
    {
        return $this->additional;
    }
}
