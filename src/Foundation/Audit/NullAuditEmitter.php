<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditEmitterInterface;
use Sif\Foundation\Contracts\AuditRecordInterface;

final readonly class NullAuditEmitter implements AuditEmitterInterface
{
    public function emit(AuditRecordInterface $record): AuditRecordInterface
    {
        return $record;
    }
}
