<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Output;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Output\ConsoleOutput;

final class ConsoleOutputTest extends TestCase
{
    public function testWritesToInjectedStreams(): void
    {
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        $output = new ConsoleOutput($stdout, $stderr);
        $output->write('out');
        $output->writeError('err');

        rewind($stdout);
        rewind($stderr);
        self::assertSame('out', stream_get_contents($stdout));
        self::assertSame('err', stream_get_contents($stderr));

        fclose($stdout);
        fclose($stderr);
    }
}
