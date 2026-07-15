<?php
declare(strict_types=1);
namespace Sif\Support\Time;
final readonly class Timer
{
    public function __construct(private int $startedAtNanoseconds, private int $stoppedAtNanoseconds) {}
    public function elapsedNanoseconds(): int { return $this->stoppedAtNanoseconds - $this->startedAtNanoseconds; }
    public function elapsedMilliseconds(): float { return $this->elapsedNanoseconds() / 1_000_000; }
}
