<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditEventInterface;
use Sif\Foundation\Contracts\AuditRecordInterface;

final readonly class AuditRecordCreated implements AuditEventInterface
{
    public function __construct(
        private AuditRecordInterface $record,
    ) {
    }

    public function record(): AuditRecordInterface
    {
        return $this->record;
    }
}
