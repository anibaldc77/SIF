<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecord;
use Sif\Foundation\Audit\AuditSubject;

interface AuditRecordFactoryInterface
{
    /**
     * @param list<string> $tags
     */
    public function create(
        ExecutionContextInterface $context,
        AuditAction $action,
        AuditLevel $level,
        AuditSubject $subject,
        AuditPayload $payload = new AuditPayload(),
        ?AuditPayload $before = null,
        ?AuditPayload $after = null,
        ?AuditPayload $changes = null,
        array $tags = [],
        string $schemaVersion = '1.0',
    ): AuditRecord;
}
