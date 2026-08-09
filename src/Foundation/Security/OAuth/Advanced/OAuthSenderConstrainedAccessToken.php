<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use DateTimeImmutable;
use InvalidArgumentException;
final readonly class OAuthSenderConstrainedAccessToken
{
    public function __construct(
        private string $serializedToken,
        private OAuthTokenConfirmation $confirmation,
        private DateTimeImmutable $expiresAt
    ) {
        if (trim($serializedToken) === '') {
            throw new InvalidArgumentException('OAuth sender-constrained access token is invalid.');
        }
    }
    public function serializedToken(): string { return $this->serializedToken; }
    public function confirmation(): OAuthTokenConfirmation { return $this->confirmation; }
    public function expiresAt(): DateTimeImmutable { return $this->expiresAt; }
}
