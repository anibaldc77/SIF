<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

final readonly class ValidationIssue
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        private string $code,
        private string $path,
        private string $message,
        private array $metadata = [],
    ) {
        if ($code === '' || $path === '' || $message === '') {
            throw new \InvalidArgumentException('Validation issue fields cannot be empty.');
        }
    }

    public function code(): string { return $this->code; }
    public function path(): string { return $this->path; }
    public function message(): string { return $this->message; }

    /** @return array<string, scalar|null> */
    public function metadata(): array { return $this->metadata; }

    /** @return array{code:string,path:string,message:string,metadata:array<string, scalar|null>} */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'path' => $this->path,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
