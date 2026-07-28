<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Redaction;

use Sif\Foundation\Logging\Contracts\SecretRedactorInterface;

final readonly class RecursiveSecretRedactor implements SecretRedactorInterface
{
    public function __construct(private SecretRedactionPolicy $policy = new SecretRedactionPolicy())
    {
    }

    public function redact(array $attributes): array
    {
        /** @var array<string, null|bool|int|float|string|array<mixed>> */
        return $this->redactArray($attributes);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    private function redactArray(array $values): array
    {
        $redacted = [];
        foreach ($values as $key => $value) {
            if (is_string($key) && $this->policy->isSensitive($key)) {
                $redacted[$key] = $this->policy->redactionMarker();
                continue;
            }
            $redacted[$key] = is_array($value) ? $this->redactArray($value) : $value;
        }
        return $redacted;
    }
}
