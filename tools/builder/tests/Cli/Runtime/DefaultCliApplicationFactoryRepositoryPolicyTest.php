<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class DefaultCliApplicationFactoryRepositoryPolicyTest extends TestCase
{
    public function testListCommandExposesRepositoryPolicyAnalyzer(): void
    {
        $application = (new DefaultCliApplicationFactory(__DIR__))->create();
        $result = $application->run(new ArgvInput(['list']));
        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertStringContainsString('repository.policy', $result->standardOutput);
    }
}
