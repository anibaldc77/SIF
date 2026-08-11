<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpDigitalCredentialsResponse
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $protocol,
        private array $payload
    ) {
        if (
            trim($this->protocol) === ''
            || $this->payload === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP Digital Credentials response is invalid.'
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
}
