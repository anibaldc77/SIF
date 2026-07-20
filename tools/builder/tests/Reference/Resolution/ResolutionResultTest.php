<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Resolution;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;

final class ResolutionResultTest extends TestCase
{
    public function testRejectsInvalidResolvedItems(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResolutionResult([new BrokenReference(new Reference('WP-102', 'ADR-001'))]);
    }

    public function testRejectsInvalidBrokenItems(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResolutionResult([], [new Reference('WP-102', 'ADR-001')]);
    }
}
