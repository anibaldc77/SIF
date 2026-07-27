<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditRecordInterface;
use Sif\Foundation\Contracts\AuditRedactionPolicyInterface;
use Sif\Foundation\Contracts\AuditSerializerInterface;
use Sif\Foundation\Contracts\ContextRedactionPolicyInterface;
use Sif\Foundation\Contracts\ContextSerializerInterface;

final readonly class AuditRecordSerializer implements AuditSerializerInterface
{
    public function __construct(
        private ContextSerializerInterface $contextSerializer,
        private ContextRedactionPolicyInterface $contextRedactionPolicy,
        private AuditRedactionPolicyInterface $auditRedactionPolicy,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(AuditRecordInterface $record): array
    {
        return [
            'schema_version' => $record->schemaVersion(),
            'audit_id' => $record->auditId()->value(),
            'action' => $record->action()->value(),
            'level' => $record->level()->value,
            'occurred_at' => $record->occurredAt()->format('Y-m-d\TH:i:s.uP'),
            'context' => $this->sortMap(
                $this->contextSerializer->serialize(
                    $record->context(),
                    $this->contextRedactionPolicy,
                ),
            ),
            'subject' => [
                'type' => $record->subject()->type(),
                'identifier' => $record->subject()->identifier(),
            ],
            'payload' => $this->serializePayload($record->payload()),
            'before' => $this->serializeOptionalPayload($record->before()),
            'after' => $this->serializeOptionalPayload($record->after()),
            'changes' => $this->serializeOptionalPayload($record->changes()),
            'tags' => $record->tags(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePayload(AuditPayload $payload): array
    {
        return $this->sortMap(
            $this->auditRedactionPolicy->redact($payload->all()),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeOptionalPayload(?AuditPayload $payload): ?array
    {
        return $payload === null ? null : $this->serializePayload($payload);
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function sortMap(array $values): array
    {
        ksort($values, SORT_STRING);

        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $values[$key] = array_is_list($value)
                ? $this->sortList($value)
                : $this->sortMap($value);
        }

        return $values;
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private function sortList(array $values): array
    {
        foreach ($values as $index => $value) {
            if (!is_array($value)) {
                continue;
            }

            $values[$index] = array_is_list($value)
                ? $this->sortList($value)
                : $this->sortMap($value);
        }

        return $values;
    }
}
