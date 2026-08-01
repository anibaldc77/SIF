<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Audit;

use DateTimeInterface;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Model\BaseModel;

final readonly class ModelAuditPayloadFactory
{
    public function subject(BaseModel $model): AuditSubject
    {
        $identity = $model->identityValues();
        $parts = [];
        foreach ($model->metadata()->identity()->names() as $name) {
            if (!array_key_exists($name, $identity) || $identity[$name] === null) {
                continue;
            }

            $value = $identity[$name];
            if (is_scalar($value)) {
                $parts[] = $name . '=' . (string) $value;
            }
        }

        return new AuditSubject(
            $model::class,
            $parts === [] ? null : implode(';', $parts),
        );
    }

    /** @param array<string, mixed> $values */
    public function payload(array $values): AuditPayload
    {
        return new AuditPayload($this->normalizeMap($values));
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @return array<string, mixed>
     */
    public function changes(array $before, array $after): array
    {
        $changes = [];
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $name) {
            $beforeValue = $before[$name] ?? null;
            $afterValue = $after[$name] ?? null;
            if ($this->equal($beforeValue, $afterValue)) {
                continue;
            }

            $changes[$name] = [
                'before' => $this->normalizeValue($beforeValue),
                'after' => $this->normalizeValue($afterValue),
            ];
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function normalizeMap(array $values): array
    {
        $normalized = [];
        foreach ($values as $name => $value) {
            $normalized[$name] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalizeValue($item);
        }

        return $normalized;
    }

    private function equal(mixed $before, mixed $after): bool
    {
        if ($before instanceof DateTimeInterface && $after instanceof DateTimeInterface) {
            return $before->format('U.uP') === $after->format('U.uP');
        }

        return $before === $after;
    }
}
