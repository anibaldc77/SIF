<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Engine\Contract\ReporterInterface;

interface ReporterSelectorInterface
{
    public function select(?string $format): ReporterInterface;
}
