<?php

declare(strict_types=1);

use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Logging\Clock\SystemClock;
use Sif\Foundation\Logging\Factory\LogRecordFactory;
use Sif\Foundation\Logging\Filtering\AcceptAllLogRecordFilter;
use Sif\Foundation\Logging\Handling\InMemoryLogHandler;
use Sif\Foundation\Logging\Handling\NullEmergencyLogReporter;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\Normalization\BoundedStructuredValueNormalizer;
use Sif\Foundation\Logging\Planning\LoggingPlan;
use Sif\Foundation\Logging\Redaction\RecursiveSecretRedactor;
use Sif\Foundation\Logging\Routing\LogRoute;
use Sif\Foundation\Logging\Routing\LogRouter;

require dirname(__DIR__) . '/vendor/autoload.php';

$handler = new InMemoryLogHandler();
$plan = new LoggingPlan(
    new LogRecordFactory(
        new SystemClock(),
        new BoundedStructuredValueNormalizer(),
        new RecursiveSecretRedactor(),
    ),
    new LogRouter([
        new LogRoute('runtime.all', new AcceptAllLogRecordFilter(), $handler),
    ], new NullEmergencyLogReporter()),
    new LogChannel('runtime'),
);

$application = (new Bootstrap(loggingPlan: $plan))->createApplication(Environment::development());
$application->boot();
$application->logger()?->info('Application ready', ['password' => 'never-published']);
$application->shutdown();

foreach ($handler->records() as $record) {
    echo $record->level()->value() . ' ' . $record->message()->template() . PHP_EOL;
}
