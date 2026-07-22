<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\DocumentationNavigation;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationEntryView;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationGroupView;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationMarkdownRenderer;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationView;

final class DocumentationNavigationMarkdownRendererTest extends TestCase
{
    public function testRendersGeneratedViewsAndDocumentGroups(): void
    {
        $view = new DocumentationNavigationView(1, [
            new DocumentationNavigationGroupView('Architecture', null, [
                new DocumentationNavigationEntryView('ADR-001', 'Core Architecture', 'ADR', 'approved', '1.0.0', 'engineering/ADR-001.md', 'ADR-001.md'),
            ]),
        ]);

        $content = (new DocumentationNavigationMarkdownRenderer())->render($view);

        self::assertStringStartsWith(DocumentationNavigationMarkdownRenderer::GENERATED_MARKER, $content);
        self::assertStringContainsString('[Repository index](INDEX.generated.md)', $content);
        self::assertStringContainsString('### Architecture', $content);
        self::assertStringContainsString('[`ADR-001`](ADR-001.md)', $content);
    }
}
