<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Extension\GenerationResult;

interface GeneratorInterface
{
    public function id(): string;

    public function generate(BuilderContext $context): GenerationResult;
}
