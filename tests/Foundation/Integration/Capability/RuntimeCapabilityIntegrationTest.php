<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Capability;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\CapabilityAwareApplicationInterface;
use Sif\Foundation\Framework;
use Sif\Foundation\ServiceProvider;

final class RuntimeCapabilityIntegrationTest extends TestCase
{
    public function testApplicationExposesTypedCapabilityRegistryWithoutChangingBaseContract(): void
    {
        $application = $this->application();

        self::assertInstanceOf(CapabilityAwareApplicationInterface::class, $application);
        self::assertSame('runtime', $application->capability(' RUNTIME ')->identifier());
        self::assertCount(5, $application->capabilityRegistry());
    }

    public function testLegacyStringFacadeUsesRegistryAsSingleSourceOfTruth(): void
    {
        $application = $this->application();
        $application->addCapability(' Runtime.Events ');

        self::assertTrue($application->capabilityRegistry()->has('runtime.events'));
        self::assertSame(
            $application->capabilityRegistry()->get('runtime.events'),
            $application->capability('runtime.events'),
        );
        self::assertSame(['runtime', 'foundation', 'providers', 'lifecycle', 'configuration', 'runtime.events'], $application->capabilities());
    }

    public function testTypedCapabilityCanBeRegisteredAndResolved(): void
    {
        $application = $this->application();
        $capability = new TestCapability('cache');

        $application->registerCapability($capability);

        self::assertSame($capability, $application->capability('cache'));
        self::assertSame([$capability], $application->capabilityRegistry()->ofType(TestCapability::class));
    }

    public function testCapabilityProviderIsDiscoveredBetweenRegisterAndBoot(): void
    {
        $application = $this->application();
        $provider = new CapabilityRecordingProvider();
        $application->providers()->add($provider);

        self::assertTrue($application->boot()->succeeded());
        self::assertSame(['register', 'capabilities', 'boot:yes'], $provider->operations);
        self::assertSame('runtime.events', $application->capability('runtime.events')->identifier());
    }

    public function testDuplicateProviderCapabilityFailsBootDeterministically(): void
    {
        $application = $this->application();
        $application->addCapability('runtime.events');
        $application->providers()->add(new CapabilityRecordingProvider());

        $result = $application->boot();

        self::assertTrue($result->failed());
        self::assertSame('capability.registration_failed', $result->errors()[0]->code());
        self::assertTrue($application->runtime()->hasFailed());
    }

    public function testNonCanonicalTypedIdentifierIsRejected(): void
    {
        $application = $this->application();

        $this->expectException(\Sif\Foundation\Exceptions\InvalidCapabilityException::class);
        $application->registerCapability(new TestCapability('Runtime.Events'));
    }

    public function testApplicationsKeepIndependentRegistries(): void
    {
        $first = $this->application();
        $second = $this->application();
        $first->registerCapability(new NamedCapability('cache'));

        self::assertTrue($first->hasCapability('cache'));
        self::assertFalse($second->hasCapability('cache'));
    }
    private function application(): CapabilityAwareApplicationInterface
    {
        $application = Framework::create();
        self::assertInstanceOf(CapabilityAwareApplicationInterface::class, $application);

        return $application;
    }
}

final readonly class TestCapability implements CapabilityInterface
{
    public function __construct(private string $identifier)
    {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}

final class CapabilityRecordingProvider extends ServiceProvider implements CapabilityProviderInterface
{
    /** @var list<string> */
    public array $operations = [];

    public function register(ApplicationInterface $application): void
    {
        $this->operations[] = 'register';
    }

    public function capabilities(): iterable
    {
        $this->operations[] = 'capabilities';

        yield new TestCapability('runtime.events');
    }

    public function boot(ApplicationInterface $application): void
    {
        $this->operations[] = 'boot:' . ($application->hasCapability('runtime.events') ? 'yes' : 'no');
    }
}
