<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

enum FederatedRevocationRetryDecision: string
{
    case Execute = 'execute';
    case ReuseCompleted = 'reuse_completed';
    case RetryIncomplete = 'retry_incomplete';
}
