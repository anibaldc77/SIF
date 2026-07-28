<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Metadata;

use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataEnricherInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureMetadataConfigurationException;

final readonly class SecretRedactingMetadataEnricher implements FailureMetadataEnricherInterface
{
    /** @var list<string> */
    private array $sensitiveKeys;

    /** @param list<string> $sensitiveKeys */
    public function __construct(
        array $sensitiveKeys = ['password', 'passwd', 'secret', 'token', 'api_key', 'apikey', 'authorization', 'cookie'],
        private string $replacement = '[redacted]',
    ) {
        if ($replacement === '') {
            throw new InvalidFailureMetadataConfigurationException('The redaction replacement must not be empty.');
        }
        $normalized = [];
        foreach ($sensitiveKeys as $key) {
            $key = strtolower(trim($key));
            if ($key === '') {
                throw new InvalidFailureMetadataConfigurationException('Sensitive metadata keys must not be empty.');
            }
            $normalized[] = $key;
        }
        $this->sensitiveKeys = array_values(array_unique($normalized));
    }

    public function enrich(array $metadata): array
    {
        return $this->redact($metadata);
    }

    /** @param array<mixed> $values
     *  @return array<mixed>
     */
    private function redact(array $values): array
    {
        $redacted = [];
        foreach ($values as $key => $value) {
            if (is_string($key) && $this->isSensitive($key)) {
                $redacted[$key] = $this->replacement;
                continue;
            }
            $redacted[$key] = is_array($value) ? $this->redact($value) : $value;
        }
        return $redacted;
    }

    private function isSensitive(string $key): bool
    {
        $canonical = strtolower(trim($key));
        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if ($canonical === $sensitiveKey || str_ends_with($canonical, '_' . $sensitiveKey) || str_ends_with($canonical, '-' . $sensitiveKey)) {
                return true;
            }
        }
        return false;
    }
}
