<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Problem;

use InvalidArgumentException;
use Throwable;

final readonly class ExceptionMapping
{
    /** @param class-string<Throwable> $throwableClass */
    public function __construct(
        private string $throwableClass,
        private int $status,
        private string $type,
        private string $title,
        private string $detail,
    ) {
        if (!is_a($throwableClass, Throwable::class, true)) {
            throw new InvalidArgumentException('Exception mapping class must implement Throwable.');
        }
        if ($status < 400 || $status > 599 || $type === '' || $title === '' || $detail === '') {
            throw new InvalidArgumentException('Exception mapping values are invalid.');
        }
    }

    /** @return class-string<Throwable> */
    public function throwableClass(): string { return $this->throwableClass; }
    public function status(): int { return $this->status; }
    public function type(): string { return $this->type; }
    public function title(): string { return $this->title; }
    public function detail(): string { return $this->detail; }
    public function matches(Throwable $throwable): bool { return $throwable instanceof $this->throwableClass; }
}
