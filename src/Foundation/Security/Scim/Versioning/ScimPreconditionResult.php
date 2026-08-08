<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

final readonly class ScimPreconditionResult
{
    public function __construct(
        private bool $satisfied,
        private ?string $reason = null
    ) {
    }

    public function satisfied(): bool
    {
        return $this->satisfied;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
