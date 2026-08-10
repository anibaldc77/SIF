<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSignedIntrospectionResponse
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        private string $serialized,
        private bool $active,
        private string $issuer,
        private string $audience,
        private array $claims = []
    ) {
        if (
            trim($this->serialized) === ''
            || trim($this->issuer) === ''
            || trim($this->audience) === ''
        ) {
            throw new InvalidArgumentException(
                'FAPI signed introspection response is invalid.'
            );
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    /**
     * @return array<string, mixed>
     */
    public function claims(): array
    {
        return $this->claims;
    }
}
