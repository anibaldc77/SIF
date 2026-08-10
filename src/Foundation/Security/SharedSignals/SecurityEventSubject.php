<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class SecurityEventSubject
{
    /**
     * @param array<string, scalar|list<scalar>|null> $attributes
     */
    public function __construct(
        private string $format,
        private array $attributes
    ) {
        if (trim($this->format) === '' || $this->attributes === []) {
            throw new InvalidArgumentException(
                'Security event subject is invalid.'
            );
        }
    }

    public function format(): string
    {
        return $this->format;
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
