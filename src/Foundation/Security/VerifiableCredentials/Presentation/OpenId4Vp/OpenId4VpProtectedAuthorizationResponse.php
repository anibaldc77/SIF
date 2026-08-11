<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpProtectedAuthorizationResponse
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        private string $serialized,
        private array $headers = []
    ) {
        if (trim($this->serialized) === '') {
            throw new InvalidArgumentException(
                'OpenID4VP protected authorization response is invalid.'
            );
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    /**
     * @return array<string, mixed>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
