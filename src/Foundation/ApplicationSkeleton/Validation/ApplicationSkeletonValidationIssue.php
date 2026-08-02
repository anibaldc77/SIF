<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Validation;

final readonly class ApplicationSkeletonValidationIssue
{
    public function __construct(
        private string $code,
        private string $message,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function message(): string
    {
        return $this->message;
    }

    /** @return array{code: string, message: string} */
    public function summary(): array
    {
        return ['code' => $this->code, 'message' => $this->message];
    }
}
