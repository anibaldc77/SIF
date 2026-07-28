<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Logging\Contracts\LoggerInterface;

interface MutableLoggingApplicationInterface extends LoggingAwareApplicationInterface
{
    public function setLogger(LoggerInterface $logger): void;
}
