<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy\Factory;

use InvalidArgumentException;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

trait PolicyFactorySupport
{
    /** @param array<string, mixed> $configuration
     *  @param list<string> $allowed
     */
    private function rejectUnknownKeys(array $configuration, array $allowed): void
    {
        $unknown = array_values(array_diff(array_keys($configuration), $allowed));
        if ($unknown !== []) {
            sort($unknown, SORT_STRING);
            throw new InvalidArgumentException(sprintf('Unknown repository policy parameter(s): %s.', implode(', ', $unknown)));
        }
    }

    /** @param array<string, mixed> $configuration */
    private function requiredString(array $configuration, string $key): string
    {
        $value = $configuration[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('Repository policy parameter "%s" must be a non-empty string.', $key));
        }
        return trim($value);
    }

    /** @param array<string, mixed> $configuration */
    private function optionalString(array $configuration, string $key): ?string
    {
        if (!array_key_exists($key, $configuration) || $configuration[$key] === null) {
            return null;
        }
        return $this->requiredString($configuration, $key);
    }

    /** @param array<string, mixed> $configuration */
    private function severity(array $configuration): DiagnosticSeverity
    {
        $value = $configuration['severity'] ?? 'error';
        if (!is_string($value)) {
            throw new InvalidArgumentException('Repository policy parameter "severity" must be a string.');
        }
        return match (strtolower(trim($value))) {
            'info' => DiagnosticSeverity::INFO,
            'warning' => DiagnosticSeverity::WARNING,
            'error' => DiagnosticSeverity::ERROR,
            'fatal' => DiagnosticSeverity::FATAL,
            default => throw new InvalidArgumentException(sprintf('Repository policy severity "%s" is invalid.', $value)),
        };
    }
}
