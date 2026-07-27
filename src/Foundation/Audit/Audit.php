<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditServiceInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Exceptions\AuditNotConfiguredException;

final class Audit
{
    private static ?AuditServiceInterface $service = null;

    private function __construct()
    {
    }

    public static function configure(AuditServiceInterface $service): void
    {
        self::$service = $service;
    }

    public static function reset(): void
    {
        self::$service = null;
    }

    public static function isConfigured(): bool
    {
        return self::$service !== null;
    }

    /**
     * @param list<string> $tags
     */
    public static function record(
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
        $service = self::$service;

        if ($service === null) {
            throw new AuditNotConfiguredException(
                'Audit facade is not configured.',
            );
        }

        return $service->record(
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
    }
}
