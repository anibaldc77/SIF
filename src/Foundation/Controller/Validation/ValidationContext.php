<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

use Sif\Foundation\Controller\Input\RequestInput;

final readonly class ValidationContext
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        private RequestInput $input,
        private array $metadata = [],
    ) {
    }

    public function input(): RequestInput
    {
        return $this->input;
    }

    /** @return array<string, scalar|null> */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
