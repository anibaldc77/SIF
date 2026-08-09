<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use InvalidArgumentException;
final readonly class OAuthDPoPVerificationContext
{
    public function __construct(
        private string $httpMethod,
        private string $httpUri,
        private ?string $accessToken = null,
        private ?string $nonce = null
    ) {
        if (trim($httpMethod)==='' || trim($httpUri)==='') {
            throw new InvalidArgumentException('OAuth DPoP verification context is invalid.');
        }
    }
    public function httpMethod(): string { return $this->httpMethod; }
    public function httpUri(): string { return $this->httpUri; }
    public function accessToken(): ?string { return $this->accessToken; }
    public function nonce(): ?string { return $this->nonce; }
}
