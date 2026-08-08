<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

use InvalidArgumentException;

final readonly class ScimPrecondition
{
    public const IF_MATCH = 'if-match';
    public const IF_NONE_MATCH = 'if-none-match';

    /**
     * @param list<ScimEntityTag> $entityTags
     */
    public function __construct(
        private string $type,
        private array $entityTags = [],
        private bool $wildcard = false
    ) {
        if (!in_array(
            $this->type,
            [self::IF_MATCH, self::IF_NONE_MATCH],
            true
        )) {
            throw new InvalidArgumentException(
                'SCIM precondition type is invalid.'
            );
        }

        if (!$this->wildcard && $this->entityTags === []) {
            throw new InvalidArgumentException(
                'SCIM precondition requires entity tags or wildcard.'
            );
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @return list<ScimEntityTag> */
    public function entityTags(): array
    {
        return $this->entityTags;
    }

    public function wildcard(): bool
    {
        return $this->wildcard;
    }
}
