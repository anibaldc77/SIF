<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextIdGeneratorInterface;

final class RandomContextIdGenerator implements ContextIdGeneratorInterface
{
    public function generate(): ContextId
    {
        return new ContextId(bin2hex(random_bytes(16)));
    }
}
