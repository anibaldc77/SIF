<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Reporting;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Exception\ReporterSelectionException;
use Sif\Builder\Cli\Reporting\ReporterSelector;

final class ReporterSelectorTest extends TestCase
{
    public function testSelectsMarkdownByDefaultAndJsonExplicitly(): void
    {
        $selector = new ReporterSelector();

        self::assertSame('report.markdown', $selector->select(null)->id());
        self::assertSame('report.json', $selector->select('json')->id());
    }

    public function testRejectsUnavailableFormat(): void
    {
        $this->expectException(ReporterSelectionException::class);

        (new ReporterSelector())->select('xml');
    }
}
