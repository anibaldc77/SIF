<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpDigitalCredentialsRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $protocol,
        private array $payload,
        private ?string $origin = null
    ) {
        if (
            trim($this->protocol) === ''
            || $this->payload === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP Digital Credentials request is invalid.'
            );
        }
    }

    public function protocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function origin(): ?string
    {
        return $this->origin;
    }
}
