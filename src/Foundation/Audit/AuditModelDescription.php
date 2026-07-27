<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

final readonly class AuditModelDescription
{
    public function __construct(
        private AuditSubject $subject,
        private AuditPayload $metadata = new AuditPayload(),
        private AuditPayload $snapshot = new AuditPayload(),
        private AuditPayload $changes = new AuditPayload(),
    ) {
    }

    public function subject(): AuditSubject
    {
        return $this->subject;
    }

    public function metadata(): AuditPayload
    {
        return $this->metadata;
    }

    public function snapshot(): AuditPayload
    {
        return $this->snapshot;
    }

    public function changes(): AuditPayload
    {
        return $this->changes;
    }
}
