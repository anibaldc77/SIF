<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use InvalidArgumentException;
final readonly class OAuthTokenConfirmation
{
    public function __construct(private string $publicKeyThumbprint)
    {
        if (trim($publicKeyThumbprint) === '') {
            throw new InvalidArgumentException('OAuth token confirmation thumbprint is invalid.');
        }
    }
    public function publicKeyThumbprint(): string { return $this->publicKeyThumbprint; }
}
