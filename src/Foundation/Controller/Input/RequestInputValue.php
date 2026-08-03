<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Input;

use Sif\Foundation\Controller\Argument\ActionArgumentSource;

final readonly class RequestInputValue
{
    public function __construct(
        private ActionArgumentSource $source,
        private string $key,
        private bool $present,
        private mixed $value = null,
    ) {
    }

    public function source(): ActionArgumentSource { return $this->source; }
    public function key(): string { return $this->key; }
    public function present(): bool { return $this->present; }
    public function value(): mixed { return $this->value; }
    public function isNull(): bool { return $this->present && $this->value === null; }
}
