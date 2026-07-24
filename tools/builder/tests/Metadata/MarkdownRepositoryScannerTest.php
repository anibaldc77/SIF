<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\CoreMetadataValidator;
use Sif\Builder\Metadata\MarkdownFrontMatterReader;
use Sif\Builder\Metadata\MarkdownRepositoryScanner;

final class MarkdownRepositoryScannerTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryDirectories = [];

    protected function tearDown(): void
    {
        foreach (array_reverse($this->temporaryDirectories) as $directory) {
            $this->removeDirectory($directory);
        }

        $this->temporaryDirectories = [];

        parent::tearDown();
    }

    public function testIndexesValidDocumentsAndReportsInvalidDocuments(): void
    {
        $root = $this->temporaryDirectory();

        file_put_contents($root . '/valid.md', $this->validDocument());
        file_put_contents($root . '/invalid.md', "---\nid: INVALID\n---\n");
        file_put_contents($root . '/ignored.txt', 'ignored');

        $result = $this->scanner()->scan($root);

        self::assertSame(1, $result->registry->count());
        self::assertTrue($result->registry->has('ES-004'));
        self::assertNotEmpty($result->issues);
        self::assertFalse($result->isSuccessful());
    }

    public function testExcludesDependencyGeneratedAndTransientDirectoriesBeforeParsing(): void
    {
        $root = $this->temporaryDirectory();
        $excludedDirectories = [
            'vendor/package',
            'tools/builder/vendor/package',
            'node_modules/package',
            '.git/docs',
            'build/docs',
            'coverage/docs',
            'tmp/docs',
            'temp/docs',
            'Vendor/package',
        ];

        foreach ($excludedDirectories as $directory) {
            $path = $root . '/' . $directory;
            $this->createDirectory($path);
            file_put_contents($path . '/invalid.md', 'no front matter');
        }

        $this->createDirectory($root . '/engineering');
        file_put_contents($root . '/engineering/valid.md', $this->validDocument());
        file_put_contents($root . '/engineering/build-profiles.md', "---\nid: INVALID\n---\n");
        file_put_contents($root . '/engineering/vendor-policy.md', "---\nid: INVALID\n---\n");
        file_put_contents($root . '/engineering/INDEX.generated.md', 'no front matter');
        file_put_contents($root . '/engineering/REFERENCES.generated.md', 'no front matter');
        file_put_contents($root . '/engineering/NAVIGATION.generated.md', 'no front matter');

        $result = $this->scanner()->scan($root);
        $issuePaths = array_map(
            static fn ($issue): string => str_replace('\\', '/', $issue->path),
            $result->issues,
        );

        $uniqueIssuePaths = array_values(array_unique($issuePaths));

        self::assertSame(1, $result->registry->count());
        self::assertCount(2, $uniqueIssuePaths);
        self::assertStringEndsWith('/engineering/build-profiles.md', $uniqueIssuePaths[0]);
        self::assertStringEndsWith('/engineering/vendor-policy.md', $uniqueIssuePaths[1]);
        self::assertStringNotContainsString('/vendor/', strtolower(implode("\n", $uniqueIssuePaths)));
        self::assertStringNotContainsString('.generated.md', strtolower(implode("\n", $uniqueIssuePaths)));
    }

    public function testProcessesCandidatesInDeterministicRelativePathOrder(): void
    {
        $root = $this->temporaryDirectory();
        $this->createDirectory($root . '/engineering');
        file_put_contents($root . '/engineering/zeta.md', "---\nid: INVALID\n---\n");
        file_put_contents($root . '/engineering/alpha.md', "---\nid: INVALID\n---\n");

        $result = $this->scanner()->scan($root);
        $issuePaths = array_map(
            static fn ($issue): string => str_replace('\\', '/', $issue->path),
            $result->issues,
        );

        $uniqueIssuePaths = array_values(array_unique($issuePaths));

        self::assertCount(2, $uniqueIssuePaths);
        self::assertStringEndsWith('/engineering/alpha.md', $uniqueIssuePaths[0]);
        self::assertStringEndsWith('/engineering/zeta.md', $uniqueIssuePaths[1]);
    }

    private function scanner(): MarkdownRepositoryScanner
    {
        return new MarkdownRepositoryScanner(new MarkdownFrontMatterReader(), new CoreMetadataValidator());
    }

    private function temporaryDirectory(): string
    {
        $processId = getmypid();
        $processToken = $processId === false ? 'unknown' : (string) $processId;
        $root = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'sif-scan-'
            . $processToken
            . '-'
            . bin2hex(random_bytes(8));

        $this->createDirectory($root);
        $this->temporaryDirectories[] = $root;

        return $root;
    }

    private function createDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            self::fail(sprintf('Unable to create temporary directory "%s".', $directory));
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = scandir($directory);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
                continue;
            }

            if (is_file($path) || is_link($path)) {
                unlink($path);
            }
        }

        rmdir($directory);
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
