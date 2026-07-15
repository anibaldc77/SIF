<?php
declare(strict_types=1);

namespace Sif\Support\Time;

use Sif\Support\Exceptions\InvalidArgumentException;

final class Stopwatch
{
    private ?int $startedAt = null;
    public function start(): void { if ($this->startedAt !== null) { throw new InvalidArgumentException('Stopwatch is already running.'); } $this->startedAt = hrtime(true); }
    public function stop(): Timer { if ($this->startedAt === null) { throw new InvalidArgumentException('Stopwatch has not been started.'); } $timer = new Timer($this->startedAt, hrtime(true)); $this->startedAt = null; return $timer; }
    public function isRunning(): bool { return $this->startedAt !== null; }
}
