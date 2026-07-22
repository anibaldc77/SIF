<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class DefaultCliApplicationFactoryReferenceIntegrityTest extends TestCase
{
    public function testListCommandExposesReferenceIntegrityAnalyzer(): void
    {
        $application = (new DefaultCliApplicationFactory(__DIR__))->create();
        $result = $application->run(new ArgvInput(['list']));

        self::assertSame(0, $result->exitCode->value);
        self::assertStringContainsString('reference.integrity', $result->standardOutput);
    }
}
