<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
final readonly class OAuthSenderConstraintValidationResult
{
    public function __construct(
        private string $tokenPublicKeyThumbprint,
        private string $proofPublicKeyThumbprint
    ) {
    }
    public function tokenPublicKeyThumbprint(): string { return $this->tokenPublicKeyThumbprint; }
    public function proofPublicKeyThumbprint(): string { return $this->proofPublicKeyThumbprint; }
    public function matches(): bool { return hash_equals($this->tokenPublicKeyThumbprint, $this->proofPublicKeyThumbprint); }
}
