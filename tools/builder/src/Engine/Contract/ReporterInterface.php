<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

use Sif\Builder\Engine\BuilderResult;

interface ReporterInterface
{
    public function id(): string;

    public function mediaType(): string;

    public function render(BuilderResult $result): string;
}
