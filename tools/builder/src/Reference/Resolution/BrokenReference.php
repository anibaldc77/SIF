<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use Sif\Builder\Reference\Reference;

final readonly class BrokenReference
{
    public const TARGET_NOT_FOUND = 'target_not_found';

    public function __construct(
        public Reference $reference,
        public string $reason = self::TARGET_NOT_FOUND,
    ) {
    }
}
