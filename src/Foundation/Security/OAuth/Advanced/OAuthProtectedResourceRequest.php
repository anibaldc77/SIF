<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
use InvalidArgumentException;
final readonly class OAuthProtectedResourceRequest
{
    public function __construct(
        private string $httpMethod,
        private string $resourceUri,
        private string $serializedAccessToken,
        private ?string $dpopProof = null
    ) {
        if (trim($httpMethod) === '' || trim($resourceUri) === '' || trim($serializedAccessToken) === '') {
            throw new InvalidArgumentException('OAuth protected resource request is invalid.');
        }
    }
    public function httpMethod(): string { return strtoupper($this->httpMethod); }
    public function resourceUri(): string { return $this->resourceUri; }
    public function serializedAccessToken(): string { return $this->serializedAccessToken; }
    public function dpopProof(): ?string { return $this->dpopProof; }
}
