<?php

declare(strict_types=1);

namespace Sif\Builder\Reference;

use InvalidArgumentException;

final readonly class ReferenceTarget
{
    public function __construct(
        public string $identifier,
        public bool $exists = false,
        public bool $resolved = false,
    ) {
        if (trim($this->identifier) === '') {
            throw new InvalidArgumentException('Reference target identifier cannot be empty.');
        }

        if ($this->resolved && !$this->exists) {
            throw new InvalidArgumentException('A resolved reference target must exist.');
        }
    }
}
