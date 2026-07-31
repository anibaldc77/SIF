<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Platform;

use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationCapabilitiesException;

final readonly class PdoMigrationCapabilities
{
    private string $lockMechanism;

    private string $lockScope;

    /** @var list<string> */
    private array $directions;

    /**
     * @param iterable<MigrationDirection> $directions
     */
    public function __construct(
        private PdoMigrationPlatform $platform,
        private bool $transactionsSupported,
        private bool $transactionalDdl,
        private bool $savepointsSupported,
        private bool $historySchemaAtomic,
        string $lockMechanism,
        string $lockScope,
        private bool $lockTimeoutSupported,
        iterable $directions,
    ) {
        $lockMechanism = strtolower(trim($lockMechanism));
        $lockScope = strtolower(trim($lockScope));

        if ($lockMechanism === '' || preg_match('/^[a-z][a-z0-9._-]*$/D', $lockMechanism) !== 1) {
            throw new InvalidPdoMigrationCapabilitiesException('Lock mechanism must be a canonical non-empty identifier.');
        }

        if (!in_array($lockScope, ['session', 'transaction'], true)) {
            throw new InvalidPdoMigrationCapabilitiesException('Lock scope must be session or transaction.');
        }

        if (($this->transactionalDdl || $this->savepointsSupported || $this->historySchemaAtomic)
            && !$this->transactionsSupported) {
            throw new InvalidPdoMigrationCapabilitiesException(
                'Transactional DDL, savepoints and atomic history coupling require transaction support.',
            );
        }

        if ($this->historySchemaAtomic && !$this->transactionalDdl) {
            throw new InvalidPdoMigrationCapabilitiesException(
                'Atomic history/schema coupling requires transactional DDL.',
            );
        }

        $normalizedDirections = [];
        foreach ($directions as $direction) {
            if (!$direction instanceof MigrationDirection) {
                throw new InvalidPdoMigrationCapabilitiesException('Directions must contain MigrationDirection values.');
            }

            if (in_array($direction->value(), $normalizedDirections, true)) {
                throw new InvalidPdoMigrationCapabilitiesException('Directions must not contain duplicates.');
            }

            $normalizedDirections[] = $direction->value();
        }

        if ($normalizedDirections === []) {
            throw new InvalidPdoMigrationCapabilitiesException('At least one migration direction must be supported.');
        }

        $this->lockMechanism = $lockMechanism;
        $this->lockScope = $lockScope;
        $this->directions = $normalizedDirections;
    }

    public static function postgresql(): self
    {
        return new self(
            PdoMigrationPlatform::postgresql(),
            true,
            true,
            true,
            true,
            'advisory-lock',
            'session',
            true,
            [MigrationDirection::up(), MigrationDirection::down()],
        );
    }

    public static function mysql(): self
    {
        return new self(
            PdoMigrationPlatform::mysql(),
            true,
            false,
            true,
            false,
            'named-lock',
            'session',
            true,
            [MigrationDirection::up(), MigrationDirection::down()],
        );
    }

    public static function sqlserver(): self
    {
        return new self(
            PdoMigrationPlatform::sqlserver(),
            true,
            true,
            true,
            true,
            'application-lock',
            'session',
            true,
            [MigrationDirection::up(), MigrationDirection::down()],
        );
    }

    public function platform(): PdoMigrationPlatform
    {
        return $this->platform;
    }

    public function transactionsSupported(): bool
    {
        return $this->transactionsSupported;
    }

    public function transactionalDdl(): bool
    {
        return $this->transactionalDdl;
    }

    public function savepointsSupported(): bool
    {
        return $this->savepointsSupported;
    }

    public function historySchemaAtomic(): bool
    {
        return $this->historySchemaAtomic;
    }

    public function lockMechanism(): string
    {
        return $this->lockMechanism;
    }

    public function lockScope(): string
    {
        return $this->lockScope;
    }

    public function lockTimeoutSupported(): bool
    {
        return $this->lockTimeoutSupported;
    }

    public function supportsDirection(MigrationDirection $direction): bool
    {
        return in_array($direction->value(), $this->directions, true);
    }

    /** @return list<string> */
    public function directions(): array
    {
        return $this->directions;
    }

    /** @return array<string, bool|string|list<string>> */
    public function summary(): array
    {
        return [
            'platform' => $this->platform->value(),
            'driver' => $this->platform->driver(),
            'transactions' => $this->transactionsSupported,
            'transactional_ddl' => $this->transactionalDdl,
            'savepoints' => $this->savepointsSupported,
            'history_schema_atomic' => $this->historySchemaAtomic,
            'lock_mechanism' => $this->lockMechanism,
            'lock_scope' => $this->lockScope,
            'lock_timeout' => $this->lockTimeoutSupported,
            'directions' => $this->directions,
        ];
    }
}
