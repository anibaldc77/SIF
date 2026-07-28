<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use Throwable;

final readonly class FailureReporterFailure
{
    public function __construct(private string $reporter, private Throwable $failure)
    {
    }

    public function reporter(): string
    {
        return $this->reporter;
    }

    public function failure(): Throwable
    {
        return $this->failure;
    }

    /** @return array{reporter:string,type:string,message:string,code:int|string} */
    public function summary(): array
    {
        return [
            'reporter' => $this->reporter,
            'type' => $this->failure::class,
            'message' => $this->failure->getMessage(),
            'code' => $this->failure->getCode(),
        ];
    }
}
