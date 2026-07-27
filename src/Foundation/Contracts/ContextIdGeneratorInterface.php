<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Context\ContextId;

/** Generates opaque execution-context identifiers. */
interface ContextIdGeneratorInterface
{
    public function generate(): ContextId;
}
