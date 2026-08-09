<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
final readonly class OAuthProtectedResourceValidationResult
{
    public function __construct(
        private bool $authorized,
        private string $subject,
        private string $clientId,
        private bool $senderConstraintValidated
    ) {
    }
    public function authorized(): bool { return $this->authorized; }
    public function subject(): string { return $this->subject; }
    public function clientId(): string { return $this->clientId; }
    public function senderConstraintValidated(): bool { return $this->senderConstraintValidated; }
}
