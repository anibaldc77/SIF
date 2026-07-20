<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\BuilderResult;

interface BuilderEngineInterface
{
    public function run(BuilderRequest $request): BuilderResult;
}
