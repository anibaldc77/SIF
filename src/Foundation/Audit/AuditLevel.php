<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

enum AuditLevel: string
{
    case Diagnostic = 'diagnostic';
    case Informational = 'informational';
    case Notice = 'notice';
    case Warning = 'warning';
    case Critical = 'critical';

    public function priority(): int
    {
        return match ($this) {
            self::Diagnostic => 100,
            self::Informational => 200,
            self::Notice => 300,
            self::Warning => 400,
            self::Critical => 500,
        };
    }

    public function atLeast(self $minimum): bool
    {
        return $this->priority() >= $minimum->priority();
    }
}
