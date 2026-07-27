<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditIdGeneratorInterface;
use Sif\Foundation\Contracts\AuditRecordFactoryInterface;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

final readonly class AuditRecordFactory implements AuditRecordFactoryInterface
{
    public function __construct(
        private AuditIdGeneratorInterface $idGenerator,
        private ClockInterface $clock,
    ) {
    }

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
    ): AuditRecord {
        return new AuditRecord(
            auditId: $this->idGenerator->generate(),
            action: $action,
            level: $level,
            occurredAt: $this->clock->now(),
            context: $context,
            subject: $subject,
            payload: $payload,
            before: $before,
            after: $after,
            changes: $changes,
            tags: $tags,
            schemaVersion: $schemaVersion,
        );
    }
}
