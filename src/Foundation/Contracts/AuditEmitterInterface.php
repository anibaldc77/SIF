<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface AuditEmitterInterface
{
    public function emit(AuditRecordInterface $record): AuditRecordInterface;
}
