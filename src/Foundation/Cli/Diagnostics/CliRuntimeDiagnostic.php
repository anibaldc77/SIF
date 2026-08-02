<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Diagnostics;

final readonly class CliRuntimeDiagnostic
{
    public function __construct(
        private string $code,
        private string $message,
        private bool $healthy,
    ) {
        if (trim($this->code) === '' || trim($this->message) === '') {
            throw new \InvalidArgumentException('Runtime diagnostic code and message cannot be blank.');
        }
    }

    public function code(): string { return $this->code; }
    public function message(): string { return $this->message; }
    public function healthy(): bool { return $this->healthy; }

    /** @return array{code: string, message: string, healthy: bool} */
    public function summary(): array
    {
        return ['code' => $this->code, 'message' => $this->message, 'healthy' => $this->healthy];
    }
}
