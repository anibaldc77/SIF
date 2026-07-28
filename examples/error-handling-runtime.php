<?php

declare(strict_types=1);

use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\ErrorHandling\Classification\OrderedThrowableClassifier;
use Sif\Foundation\ErrorHandling\Clock\SystemFailureClock;
use Sif\Foundation\ErrorHandling\Factory\FailureEnvelopeFactory;
use Sif\Foundation\ErrorHandling\Factory\RandomFailureIdGenerator;
use Sif\Foundation\ErrorHandling\Metadata\SafeFailureMetadataNormalizer;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Sif\Foundation\ErrorHandling\Recovery\OrderedRecoveryDecider;
use Sif\Foundation\ErrorHandling\Reporting\AcceptAllFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportRoute;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;
use Sif\Foundation\ErrorHandling\Reporting\InMemoryFailureReporter;
use Sif\Foundation\ErrorHandling\Reporting\NullEmergencyFailureReporter;

require dirname(__DIR__) . '/vendor/autoload.php';

$reporter = new InMemoryFailureReporter();
$plan = new ErrorHandlingPlan(
    OrderedThrowableClassifier::withUnknownFallback([]),
    new FailureEnvelopeFactory(
        new RandomFailureIdGenerator(),
        new SystemFailureClock(),
        new SafeFailureMetadataNormalizer(),
    ),
    OrderedRecoveryDecider::withRethrowFallback([]),
    new FailureReporterDispatcher([
        new FailureReportRoute('runtime', new AcceptAllFailureReportFilter(), $reporter),
    ], new NullEmergencyFailureReporter()),
);

$application = (new Bootstrap(errorHandlingPlan: $plan))
    ->createApplication(Environment::development());

$result = $application->boot();

printf("Boot succeeded: %s\n", $result->succeeded() ? 'yes' : 'no');
printf("Error handling capability: %s\n", $application->hasCapability('error-handling') ? 'yes' : 'no');
