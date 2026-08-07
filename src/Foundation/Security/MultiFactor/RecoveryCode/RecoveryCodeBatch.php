<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

final readonly class RecoveryCodeBatch
{
    /** @param list<RecoveryCode> $codes */
    public function __construct(private array $codes)
    {
        if ($codes === []) {
            throw new \InvalidArgumentException('Recovery code batch cannot be empty.');
        }
    }

    public function count(): int
    {
        return count($this->codes);
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->codes);
    }

    /** @return array{count:int,codes:string} */
    public function __debugInfo(): array
    {
        return ['count' => count($this->codes), 'codes' => '[REDACTED]'];
    }

    public function __serialize(): array
    {
        throw new \LogicException('Recovery code batches cannot be serialized.');
    }
}
