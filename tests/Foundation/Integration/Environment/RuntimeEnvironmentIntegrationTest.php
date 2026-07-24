<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Environment;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Environment;
use Sif\Foundation\Environment\ArrayEnvironmentProvider;
use Sif\Foundation\Environment\Exceptions\FrozenEnvironmentException;
use Sif\Foundation\Framework;
use Sif\Foundation\ServiceProvider;

final class RuntimeEnvironmentIntegrationTest extends TestCase
{
    private ?string $dotenv = null;

    protected function tearDown(): void
    {
        if ($this->dotenv !== null && is_file($this->dotenv)) {
            unlink($this->dotenv);
        }
    }

    public function testApplicationExposesEnvironmentRepositoryWithoutChangingDefaultCapabilities(): void
    {
        $application = $this->createEnvironmentAwareApplication();

        self::assertFalse($application->hasCapability('environment'));
        self::assertFalse($application->variables()->isFrozen());
    }

    public function testBootstrapCombinesDotenvAndNativeSourcesWithDotenvPrecedence(): void
    {
        $this->dotenv = tempnam(sys_get_temp_dir(), 'sif-env-');
        self::assertNotFalse($this->dotenv);
        file_put_contents($this->dotenv, "APP_ENV=dotenv\nAPP_PORT=8080\n");

        $native = new ArrayEnvironmentProvider(['APP_ENV' => 'native', 'HOST' => 'localhost']);
        $application = (new Bootstrap(null, [], $native, $this->dotenv))
            ->createApplication(Environment::testing());

        self::assertSame('dotenv', $application->variables()->get('APP_ENV'));
        self::assertSame('localhost', $application->variables()->get('HOST'));
        self::assertSame('8080', $application->runtime()->environment()->get('APP_PORT'));
    }

    public function testProvidersCanReadAndMutateEnvironmentBeforeFreeze(): void
    {
        $application = $this->createEnvironmentAwareApplication();
        $provider = new EnvironmentRecordingProvider();
        $application->providers()->add($provider);

        self::assertTrue($application->boot()->succeeded());
        self::assertSame(['register', 'boot:enabled'], $provider->operations);
        self::assertSame('enabled', $application->variables()->get('FEATURE_FLAG'));
    }

    public function testEnvironmentIsFrozenAfterSuccessfulBoot(): void
    {
        $application = $this->createEnvironmentAwareApplication();
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->variables()->isFrozen());

        $this->expectException(FrozenEnvironmentException::class);
        $application->variables()->set('APP_ENV', 'changed');
    }

    public function testFailedBootLeavesEnvironmentMutable(): void
    {
        $application = $this->createEnvironmentAwareApplication();
        $application->providers()->add(new FailingEnvironmentProvider());

        self::assertTrue($application->boot()->failed());
        self::assertFalse($application->variables()->isFrozen());
    }

    private function createEnvironmentAwareApplication(): EnvironmentAwareApplicationInterface
    {
        $application = Framework::create();

        if (!$application instanceof EnvironmentAwareApplicationInterface) {
            self::fail('Framework must create an environment-aware application.');
        }

        return $application;
    }
}

final class EnvironmentRecordingProvider extends ServiceProvider
{
    /** @var list<string> */
    public array $operations = [];

    public function register(ApplicationInterface $application): void
    {
        if (!$application instanceof EnvironmentAwareApplicationInterface) {
            return;
        }
        $this->operations[] = 'register';
        $application->variables()->set('FEATURE_FLAG', 'enabled');
    }

    public function boot(ApplicationInterface $application): void
    {
        if ($application instanceof EnvironmentAwareApplicationInterface) {
            $this->operations[] = 'boot:' . $application->variables()->get('FEATURE_FLAG');
        }
    }
}

final class FailingEnvironmentProvider extends ServiceProvider
{
    public function register(ApplicationInterface $application): void
    {
    }

    public function boot(ApplicationInterface $application): void
    {
        throw new \RuntimeException('environment boot failure');
    }
}
