<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;

interface MutableErrorHandlingApplicationInterface extends ErrorHandlingAwareApplicationInterface
{
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void;
}
