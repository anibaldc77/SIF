<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\CoreMetadataValidator;
use Sif\Builder\Metadata\MarkdownFrontMatterReader;
use Sif\Builder\Metadata\MarkdownRepositoryScanner;

final class MarkdownRepositoryScannerTest extends TestCase
{
    public function testIndexesValidDocumentsAndReportsInvalidDocuments(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sif-scan-' . bin2hex(random_bytes(4));
        mkdir($root, 0777, true);

        file_put_contents($root . '/valid.md', $this->validDocument());
        file_put_contents($root . '/invalid.md', "---\nid: INVALID\n---\n");
        file_put_contents($root . '/ignored.txt', 'ignored');

        $scanner = new MarkdownRepositoryScanner(new MarkdownFrontMatterReader(), new CoreMetadataValidator());
        $result = $scanner->scan($root);

        self::assertSame(1, $result->registry->count());
        self::assertTrue($result->registry->has('ES-004'));
        self::assertNotEmpty($result->issues);
        self::assertFalse($result->isSuccessful());
    }

    private function validDocument(): string
    {
        return <<<'MD'
---
id: ES-004
title: Markdown Convention
status: Draft for Review
version: 0.1.0
category: Engineering Standard
document_class: NormativeDocument
authors: [SIF Architecture Board]
created: 2026-07-17
updated: 2026-07-17
tags: [documentation, markdown]
work_package: WP-100
depends_on: [ES-001, ES-002]
related_adrs: []
supersedes: null
superseded_by: null
---

# Markdown Convention
MD;
    }
}
