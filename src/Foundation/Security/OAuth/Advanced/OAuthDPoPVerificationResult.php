<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use DateTimeImmutable;
final readonly class OAuthDPoPVerificationResult
{
    public function __construct(private string $publicKeyThumbprint, private string $tokenId, private DateTimeImmutable $issuedAt) {}
    public function publicKeyThumbprint(): string { return $this->publicKeyThumbprint; }
    public function tokenId(): string { return $this->tokenId; }
    public function issuedAt(): DateTimeImmutable { return $this->issuedAt; }
}
