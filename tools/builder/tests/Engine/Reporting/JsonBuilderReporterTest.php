<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Reporting\JsonBuilderReporter;

final class JsonBuilderReporterTest extends TestCase
{
    public function testItRendersValidStructuredJson(): void
    {
        $content = (new JsonBuilderReporter())->render(
            BuilderResult::succeeded([BuilderPhase::COMPLETED], runIdentifier: 'run-001'),
        );
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('succeeded', $decoded['status']);
        self::assertSame('run-001', $decoded['run_identifier']);
        self::assertSame(1, $decoded['statistics']['completed_phase_count']);
        self::assertArrayNotHasKey('cause', $decoded);
    }
}
