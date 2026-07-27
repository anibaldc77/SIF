<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditSubject;

interface AuditModelAdapterInterface
{
    public function subject(object $model): AuditSubject;

    public function metadata(object $model): AuditPayload;

    public function snapshot(object $model): AuditPayload;
}
