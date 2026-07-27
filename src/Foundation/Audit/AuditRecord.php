<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use DateTimeImmutable;
use Sif\Foundation\Contracts\AuditRecordInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Exceptions\InvalidAuditRecordException;

final readonly class AuditRecord implements AuditRecordInterface
{
    /**
     * @var list<string>
     */
    private array $tags;

    /**
     * @param list<string> $tags
     */
    public function __construct(
        private AuditId $auditId,
        private AuditAction $action,
        private AuditLevel $level,
        private DateTimeImmutable $occurredAt,
        private ExecutionContextInterface $context,
        private AuditSubject $subject,
        private AuditPayload $payload = new AuditPayload(),
        private ?AuditPayload $before = null,
        private ?AuditPayload $after = null,
        private ?AuditPayload $changes = null,
        array $tags = [],
        private string $schemaVersion = '1.0',
    ) {
        $this->tags = self::validateTags($tags);

        if (trim($this->schemaVersion) === '') {
            throw new InvalidAuditRecordException('Audit schema version cannot be empty.');
        }
    }

    public function auditId(): AuditId
    {
        return $this->auditId;
    }

    public function action(): AuditAction
    {
        return $this->action;
    }

    public function level(): AuditLevel
    {
        return $this->level;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function context(): ExecutionContextInterface
    {
        return $this->context;
    }

    public function subject(): AuditSubject
    {
        return $this->subject;
    }

    public function payload(): AuditPayload
    {
        return $this->payload;
    }

    public function before(): ?AuditPayload
    {
        return $this->before;
    }

    public function after(): ?AuditPayload
    {
        return $this->after;
    }

    public function changes(): ?AuditPayload
    {
        return $this->changes;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
    }

    /**
     * @param list<string> $tags
     *
     * @return list<string>
     */
    private static function validateTags(array $tags): array
    {
        $normalized = [];

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw new InvalidAuditRecordException('Audit tags cannot be empty.');
            }

            if (in_array($tag, $normalized, true)) {
                continue;
            }

            $normalized[] = $tag;
        }

        return $normalized;
    }
}
