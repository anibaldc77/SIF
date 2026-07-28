<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Logging\Contracts\LoggerInterface;

interface LoggingAwareApplicationInterface extends ApplicationInterface
{
    public function logger(): ?LoggerInterface;
}
