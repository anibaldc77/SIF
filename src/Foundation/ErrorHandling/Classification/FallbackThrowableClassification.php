<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Classification;

use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureSeverity;

final class FallbackThrowableClassification
{
    private function __construct()
    {
    }

    public static function unknown(): ThrowableClassification
    {
        return new ThrowableClassification(
            FailureCategory::unknown(),
            FailureSeverity::error(),
            FailureDisposition::unknown(),
            'fallback.unknown',
            true,
        );
    }
}
