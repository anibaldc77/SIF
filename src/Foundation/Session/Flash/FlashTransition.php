<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Flash;

final readonly class FlashTransition
{
    /**
     * @param array<string, mixed> $available
     * @param array<string, mixed> $new
     * @param array<string, true> $kept
     */
    public function __construct(
        private array $available,
        private array $new,
        private array $kept,
    ) {
    }

    /** @return array<string, mixed> */
    public function nextRequestData(): array
    {
        $retained = [];
        foreach ($this->kept as $key => $_) {
            if (array_key_exists($key, $this->available)) {
                $retained[$key] = $this->available[$key];
            }
        }

        return array_replace($retained, $this->new);
    }
}
