<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsStream;

interface SharedSignalsStreamRepositoryInterface
{
    public function find(string $streamId): ?SharedSignalsStream;

    public function save(SharedSignalsStream $stream): void;

    public function delete(string $streamId): void;
}
