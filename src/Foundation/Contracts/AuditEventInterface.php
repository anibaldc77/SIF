<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface AuditEventInterface
{
    public function record(): AuditRecordInterface;
}
