<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryIndex;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexEntryView;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexMarkdownRenderer;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexSection;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexView;

final class RepositoryIndexMarkdownRendererTest extends TestCase
{
    public function testRendersDeterministicallyAndEscapesTableContent(): void
    {
        $view = new RepositoryIndexView(1, 2, 1, [
            new RepositoryIndexSection('ADR', [
                new RepositoryIndexEntryView('ADR-001', 'Title | line', 'ADR', 'accepted', '1.0', 'engineering/ADR-001.md', 'ADR-001.md'),
            ]),
        ], ['accepted' => 1], ['ADR' => 1]);
        $renderer = new RepositoryIndexMarkdownRenderer();

        $first = $renderer->render($view);
        $second = $renderer->render($view);

        self::assertSame($first, $second);
        self::assertStringStartsWith(RepositoryIndexMarkdownRenderer::GENERATED_MARKER, $first);
        self::assertStringContainsString('Title \\| line', $first);
        self::assertStringContainsString('Warning: 1 unresolved reference detected.', $first);
        self::assertStringEndsWith("\n", $first);
    }
}
