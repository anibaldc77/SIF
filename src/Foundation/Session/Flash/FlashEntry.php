<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Flash;

final readonly class FlashEntry
{
    public function __construct(
        private string $key,
        private mixed $value,
        private FlashState $state,
    ) {
    }

    public function key(): string { return $this->key; }
    public function value(): mixed { return $this->value; }
    public function state(): FlashState { return $this->state; }
}
