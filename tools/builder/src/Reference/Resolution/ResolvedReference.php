<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use InvalidArgumentException;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final readonly class ResolvedReference
{
    public function __construct(
        public Reference $reference,
        public RepositoryIndexEntry $target,
    ) {
        if ($this->reference->targetIdentifier !== $this->target->identifier) {
            throw new InvalidArgumentException(sprintf(
                'Resolved target identifier "%s" does not match reference target "%s".',
                $this->target->identifier,
                $this->reference->targetIdentifier,
            ));
        }
    }
}
