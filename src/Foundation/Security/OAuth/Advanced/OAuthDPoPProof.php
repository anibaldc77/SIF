<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use DateTimeImmutable;
use InvalidArgumentException;
final readonly class OAuthDPoPProof
{
    public function __construct(
        private string $serialized,
        private string $httpMethod,
        private string $httpUri,
        private DateTimeImmutable $issuedAt,
        private string $tokenId,
        private string $publicKeyThumbprint,
        private ?string $accessTokenHash = null,
        private ?string $nonce = null
    ) {
        if (trim($serialized)==='' || trim($httpMethod)==='' || trim($httpUri)==='' || trim($tokenId)==='' || trim($publicKeyThumbprint)==='') {
            throw new InvalidArgumentException('OAuth DPoP proof is invalid.');
        }
    }
    public function serialized(): string { return $this->serialized; }
    public function httpMethod(): string { return $this->httpMethod; }
    public function httpUri(): string { return $this->httpUri; }
    public function issuedAt(): DateTimeImmutable { return $this->issuedAt; }
    public function tokenId(): string { return $this->tokenId; }
    public function publicKeyThumbprint(): string { return $this->publicKeyThumbprint; }
    public function accessTokenHash(): ?string { return $this->accessTokenHash; }
    public function nonce(): ?string { return $this->nonce; }
}
