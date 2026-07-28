<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Orchestration;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportingResult;

final readonly class ErrorHandlingResult
{
    public function __construct(
        private ThrowableClassification $classification,
        private FailureEnvelope $envelope,
        private RecoveryDecision $recoveryDecision,
        private FailureReportingResult $reportingResult,
    ) {
    }

    public function classification(): ThrowableClassification { return $this->classification; }
    public function envelope(): FailureEnvelope { return $this->envelope; }
    public function recoveryDecision(): RecoveryDecision { return $this->recoveryDecision; }
    public function reportingResult(): FailureReportingResult { return $this->reportingResult; }

    /** @return array{classification:array{category:string,severity:string,disposition:string,rule:string,fallback:bool},failure:array{id:string,occurred_at:string,category:string,severity:string,disposition:string,origin:string,throwable:array{type:string,message:string,code:int|string},metadata:array<string, null|bool|int|float|string|array<mixed>>},recovery:array{action:string,policy:string,fallback:bool,retry:?array{attempt:int,maximum_attempts:int,delay_milliseconds:int,remaining:bool}},reporting:array{reported_routes:list<string>,filtered_routes:list<string>,failure_count:int,succeeded:bool}} */
    public function summary(): array
    {
        return [
            'classification' => $this->classification->summary(),
            'failure' => $this->envelope->summary(),
            'recovery' => $this->recoveryDecision->summary(),
            'reporting' => [
                'reported_routes' => $this->reportingResult->reportedRoutes(),
                'filtered_routes' => $this->reportingResult->filteredRoutes(),
                'failure_count' => $this->reportingResult->failureCount(),
                'succeeded' => $this->reportingResult->succeeded(),
            ],
        ];
    }
}
