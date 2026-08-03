<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

final readonly class ActionArgumentIssue
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        private string $code,
        private string $argument,
        private ActionArgumentSource $source,
        private string $message,
        private array $metadata = [],
    ) {
    }

    public function code(): string { return $this->code; }
    public function argument(): string { return $this->argument; }
    public function source(): ActionArgumentSource { return $this->source; }
    public function message(): string { return $this->message; }
    /** @return array<string, scalar|null> */
    public function metadata(): array { return $this->metadata; }
}
