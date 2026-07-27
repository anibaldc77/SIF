<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditSubject;

interface AuditRecordInterface
{
    public function auditId(): AuditId;

    public function action(): AuditAction;

    public function level(): AuditLevel;

    public function occurredAt(): DateTimeImmutable;

    public function context(): ExecutionContextInterface;

    public function subject(): AuditSubject;

    public function payload(): AuditPayload;

    public function before(): ?AuditPayload;

    public function after(): ?AuditPayload;

    public function changes(): ?AuditPayload;

    /**
     * @return list<string>
     */
    public function tags(): array;

    public function schemaVersion(): string;
}
