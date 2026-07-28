<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use Throwable;

final readonly class ThrowableMetadata
{
    public function __construct(
        private string $type,
        private string $message,
        private int|string $code,
    ) {
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable::class, $throwable->getMessage(), $throwable->getCode());
    }

    public function type(): string { return $this->type; }
    public function message(): string { return $this->message; }
    public function code(): int|string { return $this->code; }

    /** @return array{type: string, message: string, code: int|string} */
    public function toArray(): array
    {
        return ['type' => $this->type, 'message' => $this->message, 'code' => $this->code];
    }
}
