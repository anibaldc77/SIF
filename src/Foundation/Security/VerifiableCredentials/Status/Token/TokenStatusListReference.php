<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Token;

use InvalidArgumentException;

final readonly class TokenStatusListReference
{
    public function __construct(
        private string $uri,
        private int $index
    ) {
        if (
            trim($this->uri) === ''
            || $this->index < 0
        ) {
            throw new InvalidArgumentException(
                'Token Status List reference is invalid.'
            );
        }
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function index(): int
    {
        return $this->index;
    }
}
