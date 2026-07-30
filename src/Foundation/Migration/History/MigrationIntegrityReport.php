<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\History;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryException;

final readonly class MigrationIntegrityReport
{
    /** @var list<string> */
    private array $missingFromRegistry;
    /** @var list<string> */
    private array $checksumMismatches;
    /** @var list<string> */
    private array $pending;

    /**
     * @param iterable<string> $missingFromRegistry
     * @param iterable<string> $checksumMismatches
     * @param iterable<string> $pending
     */
    public function __construct(iterable $missingFromRegistry, iterable $checksumMismatches, iterable $pending)
    {
        $this->missingFromRegistry = self::normalize($missingFromRegistry);
        $this->checksumMismatches = self::normalize($checksumMismatches);
        $this->pending = self::normalize($pending);
    }

    public function isValid(): bool
    {
        return $this->missingFromRegistry === [] && $this->checksumMismatches === [];
    }

    /** @return list<string> */
    public function missingFromRegistry(): array
    {
        return $this->missingFromRegistry;
    }

    /** @return list<string> */
    public function checksumMismatches(): array
    {
        return $this->checksumMismatches;
    }

    /** @return list<string> */
    public function pending(): array
    {
        return $this->pending;
    }

    /**
     * @param iterable<string> $values
     * @return list<string>
     */
    private static function normalize(iterable $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value) || $value === '') {
                throw new InvalidMigrationHistoryException('Migration integrity identifiers must be non-empty strings.');
            }
            $normalized[$value] = true;
        }
        $result = array_keys($normalized);
        sort($result, SORT_STRING);
        return $result;
    }
}
