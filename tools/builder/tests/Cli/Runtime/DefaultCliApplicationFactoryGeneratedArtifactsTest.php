<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class DefaultCliApplicationFactoryGeneratedArtifactsTest extends TestCase
{
    public function testListCommandExposesGeneratedArtifactsAnalyzer(): void
    {
        $result = (new DefaultCliApplicationFactory(__DIR__))->create()->run(new ArgvInput(['list']));

        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertStringContainsString('  - generated.artifacts', $result->standardOutput);
    }
}
