<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Exceptions;

use RuntimeException;
use Sif\Foundation\Controller\Argument\ActionArgumentIssue;

final class ControllerArgumentResolutionException extends RuntimeException
{
    /** @param list<ActionArgumentIssue> $issues */
    public function __construct(private readonly array $issues)
    {
        parent::__construct('Controller action arguments could not be resolved.');
    }

    /** @return list<ActionArgumentIssue> */
    public function issues(): array
    {
        return $this->issues;
    }
}
