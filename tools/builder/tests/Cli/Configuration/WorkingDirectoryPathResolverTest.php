<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Configuration\WorkingDirectoryPathResolver;
use Sif\Builder\Cli\Exception\RequestMappingException;

final class WorkingDirectoryPathResolverTest extends TestCase
{
    public function testItResolvesRelativeAndNormalizesAbsolutePaths(): void
    {
        $resolver = new WorkingDirectoryPathResolver('D:\\SIF');

        self::assertSame('D:/SIF/tools/output', $resolver->resolve('./tools/../tools/output'));
        self::assertSame('C:/repo', $resolver->resolve('c:\\repo'));
    }

    public function testItRejectsTraversalBeyondTheAbsoluteRoot(): void
    {
        $this->expectException(RequestMappingException::class);

        (new WorkingDirectoryPathResolver('/workspace'))->resolve('/../../secret');
    }
}
