<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Planning;

use Sif\Foundation\ErrorHandling\Contracts\FailureEnvelopeFactoryInterface;
use Sif\Foundation\ErrorHandling\Contracts\RecoveryDeciderInterface;
use Sif\Foundation\ErrorHandling\Contracts\ThrowableClassifierInterface;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;

final readonly class ErrorHandlingPlan
{
    public function __construct(
        private ThrowableClassifierInterface $classifier,
        private FailureEnvelopeFactoryInterface $envelopeFactory,
        private RecoveryDeciderInterface $recoveryDecider,
        private FailureReporterDispatcher $reporterDispatcher,
    ) {
    }

    public function classifier(): ThrowableClassifierInterface { return $this->classifier; }
    public function envelopeFactory(): FailureEnvelopeFactoryInterface { return $this->envelopeFactory; }
    public function recoveryDecider(): RecoveryDeciderInterface { return $this->recoveryDecider; }
    public function reporterDispatcher(): FailureReporterDispatcher { return $this->reporterDispatcher; }
}
