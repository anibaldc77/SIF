<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditRedactionPolicyInterface;
use Sif\Foundation\Exceptions\InvalidAuditRedactionPolicyException;

final readonly class AuditRedactionPolicy implements AuditRedactionPolicyInterface
{
    /**
     * @var list<string>
     */
    private array $keys;

    /**
     * @param list<string> $keys
     */
    public function __construct(
        array $keys,
        private string $marker = '[REDACTED]',
    ) {
        foreach ($keys as $key) {
            if (trim($key) === '') {
                throw new InvalidAuditRedactionPolicyException(
                    'Audit redaction keys cannot be empty.',
                );
            }
        }

        if (trim($this->marker) === '') {
            throw new InvalidAuditRedactionPolicyException(
                'Audit redaction marker cannot be empty.',
            );
        }

        $this->keys = array_values(array_unique($keys));
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    public function redact(array $values): array
    {
        return $this->redactMap($values);
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function redactMap(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $this->keys, true)) {
                $redacted[$key] = $this->marker;
                continue;
            }

            $redacted[$key] = is_array($value)
                ? $this->redactArray($value)
                : $value;
        }

        return $redacted;
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<mixed>
     */
    private function redactArray(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && in_array($key, $this->keys, true)) {
                $redacted[$key] = $this->marker;
                continue;
            }

            $redacted[$key] = is_array($value)
                ? $this->redactArray($value)
                : $value;
        }

        return $redacted;
    }
}
