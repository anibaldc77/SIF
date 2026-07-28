<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Logging\Clock\FrozenClock;
use Sif\Foundation\Logging\Factory\LogRecordFactory;
use Sif\Foundation\Logging\Filtering\AcceptAllLogRecordFilter;
use Sif\Foundation\Logging\Handling\InMemoryLogHandler;
use Sif\Foundation\Logging\Handling\NullEmergencyLogReporter;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\Normalization\BoundedStructuredValueNormalizer;
use Sif\Foundation\Logging\Planning\LoggingPlan;
use Sif\Foundation\Logging\Redaction\RecursiveSecretRedactor;
use Sif\Foundation\Logging\Routing\LogRoute;
use Sif\Foundation\Logging\Routing\LogRouter;
use Sif\Foundation\Logging\Runtime\RuntimeLoggingServiceProvider;

final class LoggingRuntimeIntegrationTest extends TestCase
{
    public function testBootstrapWithoutLoggingPlanRemainsCompatible(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->logger());
        self::assertFalse($application->hasCapability('logging'));
        self::assertFalse($application->providers()->has(RuntimeLoggingServiceProvider::class));
    }

    public function testBootstrapPublishesLoggerProviderAndCapabilityWhenConfigured(): void
    {
        [$plan, $handler] = $this->plan();
        $application = (new Bootstrap(loggingPlan: $plan))->createApplication(Environment::testing());

        self::assertNotNull($application->logger());
        self::assertTrue($application->providers()->has(RuntimeLoggingServiceProvider::class));

        $result = $application->boot();

        self::assertTrue($result->succeeded());
        self::assertTrue($application->hasCapability('logging'));
        self::assertSame(2, $handler->count());
        self::assertSame(['register', 'boot'], array_map(
            static fn ($record): string => self::phase($record->attributes()),
            $handler->records(),
        ));
    }

    public function testRuntimeLoggingProviderIsLastDuringShutdown(): void
    {
        [$plan, $handler] = $this->plan();
        $application = (new Bootstrap(loggingPlan: $plan))->createApplication(Environment::testing());
        $application->boot();

        $result = $application->shutdown();

        self::assertTrue($result->succeeded());
        self::assertSame('shutdown', $handler->records()[2]->attributes()['phase']);
    }

    public function testApplicationExposesTheSameConfiguredLogger(): void
    {
        [$plan] = $this->plan();
        $application = (new Bootstrap(loggingPlan: $plan))->createApplication(Environment::testing());
        $logger = $application->logger();

        self::assertNotNull($logger);
        self::assertSame($logger, $application->logger());
    }

    /** @param array<string, array<array<array-key, mixed>|bool|float|int|string|null>|bool|float|int|string|null> $attributes */
    private static function phase(array $attributes): string
    {
        $phase = $attributes['phase'] ?? null;

        self::assertIsString($phase);

        return $phase;
    }

    /** @return array{0: LoggingPlan, 1: InMemoryLogHandler} */
    private function plan(): array
    {
        $handler = new InMemoryLogHandler();
        $factory = new LogRecordFactory(
            new FrozenClock(new LogTimestamp(new DateTimeImmutable('2026-07-28T22:00:00.000000-03:00'))),
            new BoundedStructuredValueNormalizer(),
            new RecursiveSecretRedactor(),
        );
        $router = new LogRouter([
            new LogRoute('runtime.all', new AcceptAllLogRecordFilter(), $handler),
        ], new NullEmergencyLogReporter());

        return [new LoggingPlan($factory, $router, new LogChannel('runtime')), $handler];
    }
}
