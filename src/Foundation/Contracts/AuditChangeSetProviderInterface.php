<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Audit\AuditPayload;

interface AuditChangeSetProviderInterface
{
    public function auditChanges(): AuditPayload;
}
