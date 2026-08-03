<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

final readonly class RouteDiagnostic
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        private string $code,
        private string $message,
        private string $severity = 'error',
        private array $metadata = [],
    ) {
        if ($code === '' || trim($code) !== $code) {
            throw new \InvalidArgumentException('Route diagnostic codes must be non-empty and trimmed.');
        }
        if (!in_array($severity, ['error', 'warning'], true)) {
            throw new \InvalidArgumentException('Route diagnostic severity must be error or warning.');
        }
    }

    public function code(): string { return $this->code; }
    public function message(): string { return $this->message; }
    public function severity(): string { return $this->severity; }
    /** @return array<string, scalar|null> */ public function metadata(): array { return $this->metadata; }

    /** @return array{code:string,message:string,severity:string,metadata:array<string, scalar|null>} */
    public function toArray(): array
    {
        return ['code' => $this->code, 'message' => $this->message, 'severity' => $this->severity, 'metadata' => $this->metadata];
    }
}
