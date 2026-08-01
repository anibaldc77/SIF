<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Platform;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoPersistenceCapabilitiesException;

final readonly class PdoPersistenceCapabilities
{
    /** @var list<string> */
    private array $supportedOperators;

    /**
     * @param iterable<string> $supportedOperators
     */
    public function __construct(
        private PdoPersistencePlatform $platform,
        private bool $transactionsSupported,
        private bool $savepointsSupported,
        private bool $returningSupported,
        private bool $offsetPaginationSupported,
        private int $maximumParameterCount,
        iterable $supportedOperators,
    ) {
        if ($this->savepointsSupported && !$this->transactionsSupported) {
            throw new InvalidPdoPersistenceCapabilitiesException('Savepoints require transaction support.');
        }
        if ($this->maximumParameterCount < 1) {
            throw new InvalidPdoPersistenceCapabilitiesException('Maximum parameter count must be positive.');
        }

        $operators = [];
        foreach ($supportedOperators as $operator) {
            $normalized = strtolower(trim($operator));
            if ($normalized === '' || preg_match('/^[a-z][a-z0-9._-]*$/D', $normalized) !== 1) {
                throw new InvalidPdoPersistenceCapabilitiesException('Operators must use canonical identifiers.');
            }
            if (in_array($normalized, $operators, true)) {
                throw new InvalidPdoPersistenceCapabilitiesException('Operators must not contain duplicates.');
            }
            $operators[] = $normalized;
        }
        if ($operators === []) {
            throw new InvalidPdoPersistenceCapabilitiesException('At least one query operator must be supported.');
        }
        $this->supportedOperators = $operators;
    }

    public static function postgresql(): self
    {
        return new self(PdoPersistencePlatform::postgresql(), true, true, true, true, 65535, self::defaultOperators());
    }

    public static function mysql(): self
    {
        return new self(PdoPersistencePlatform::mysql(), true, true, false, true, 65535, self::defaultOperators());
    }

    public static function sqlserver(): self
    {
        return new self(PdoPersistencePlatform::sqlserver(), true, true, true, true, 2100, self::defaultOperators());
    }

    public function platform(): PdoPersistencePlatform { return $this->platform; }
    public function transactionsSupported(): bool { return $this->transactionsSupported; }
    public function savepointsSupported(): bool { return $this->savepointsSupported; }
    public function returningSupported(): bool { return $this->returningSupported; }
    public function offsetPaginationSupported(): bool { return $this->offsetPaginationSupported; }
    public function maximumParameterCount(): int { return $this->maximumParameterCount; }
    public function supportsOperator(string $operator): bool { return in_array(strtolower(trim($operator)), $this->supportedOperators, true); }
    /** @return list<string> */ public function supportedOperators(): array { return $this->supportedOperators; }

    /** @return array<string, bool|int|string|list<string>> */
    public function summary(): array
    {
        return [
            'platform' => $this->platform->value(),
            'driver' => $this->platform->driver(),
            'transactions' => $this->transactionsSupported,
            'savepoints' => $this->savepointsSupported,
            'returning' => $this->returningSupported,
            'offset_pagination' => $this->offsetPaginationSupported,
            'maximum_parameters' => $this->maximumParameterCount,
            'operators' => $this->supportedOperators,
        ];
    }

    /** @return list<string> */
    private static function defaultOperators(): array
    {
        return ['equal', 'not-equal', 'greater-than', 'greater-than-or-equal', 'less-than', 'less-than-or-equal', 'like', 'in', 'is-null', 'is-not-null'];
    }
}
