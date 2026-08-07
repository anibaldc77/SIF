<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Http;

use InvalidArgumentException;

final readonly class OidcRedirectInstruction
{
    /**
     * @param array<string,string> $query
     */
    public function __construct(
        private string $location,
        private array $query = []
    ) {
        if ($this->location === '' || strlen($this->location) > 4096) {
            throw new InvalidArgumentException(
                'OIDC redirect location is invalid.'
            );
        }
    }

    public function location(): string
    {
        return $this->location;
    }

    /** @return array<string,string> */
    public function query(): array
    {
        return $this->query;
    }
}
