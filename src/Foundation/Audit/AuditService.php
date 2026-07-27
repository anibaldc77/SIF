<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditEmitterInterface;
use Sif\Foundation\Contracts\AuditRecordFactoryInterface;
use Sif\Foundation\Contracts\AuditServiceInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

final readonly class AuditService implements AuditServiceInterface
{
    public function __construct(
        private AuditRecordFactoryInterface $factory,
        private AuditEmitterInterface $emitter,
    ) {
    }

    /**
     * @param list<string> $tags
     */
    public function record(
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
        $record = $this->factory->create(
            context: $context,
            action: $action,
            level: $level,
            subject: $subject,
            payload: $payload,
            before: $before,
            after: $after,
            changes: $changes,
            tags: $tags,
            schemaVersion: $schemaVersion,
        );

        $emitted = $this->emitter->emit($record);

        return $emitted instanceof AuditRecord
            ? $emitted
            : $record;
    }
}
