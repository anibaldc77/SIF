<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class DefaultCliApplicationFactoryDocumentationNavigationTest extends TestCase
{
    public function testListCommandExposesDocumentationNavigationGenerator(): void
    {
        $application = (new DefaultCliApplicationFactory('D:/SIF'))->create();
        $result = $application->run(new ArgvInput(['list']));

        self::assertSame(0, $result->exitCode->value);
        self::assertStringContainsString('documentation.navigation', $result->standardOutput);
    }
}
