<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class BuiltInAnalyzersEndToEndTest extends TestCase
{
    private string $sandbox;
    private string $repository;
    private string $output;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/sif-builder-wp106-' . bin2hex(random_bytes(6));
        $this->repository = $this->sandbox . '/repository';
        $this->output = $this->sandbox . '/output';

        mkdir($this->repository . '/engineering/decisions', 0777, true);
        mkdir($this->repository . '/engineering/work-packages', 0777, true);

        file_put_contents(
            $this->repository . '/engineering/decisions/ADR-001.md',
            $this->document(
                id: 'ADR-001',
                title: 'Analyzer architecture',
                category: 'Architecture Decision Record',
                documentClass: 'GovernanceDocument',
                related: [],
                workPackage: 'WP-106',
            ),
        );

        file_put_contents(
            $this->repository . '/engineering/work-packages/WP-106.md',
            $this->document(
                id: 'WP-106',
                title: 'Built-in analyzers',
                category: 'Work Package',
                documentClass: 'GovernanceDocument',
                related: ['ADR-001'],
                workPackage: 'WP-106',
            ),
        );
    }

    protected function tearDown(): void
    {
        $this->remove($this->sandbox);
    }

    public function testListExposesAllBuiltInAnalyzersInGovernedOrder(): void
    {
        $application = (new DefaultCliApplicationFactory($this->sandbox))->create();
        $result = $application->run(new ArgvInput(['list']));

        self::assertSame(0, $result->exitCode->value);
        $normalizedOutput = str_replace(["\r\n", "\r"], "\n", $result->standardOutput);

        self::assertStringContainsString(
            implode("\n", [
                'Analyzers:',
                '  - metadata.completeness',
                '  - reference.integrity',
                '  - document.consistency',
                '  - repository.policy',
                '  - generated.artifacts',
            ]),
            $normalizedOutput,
        );
    }

    public function testStrictBuildRunsAllAnalyzersAndStillGeneratesWhenOnlyWarningsExist(): void
    {
        $application = (new DefaultCliApplicationFactory($this->sandbox))->create();
        $result = $application->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--format=json',
            '--strict',
        ]));

        self::assertSame(0, $result->exitCode->value, $result->standardError ?? '');
        self::assertNotNull($result->builderResult);
        self::assertSame(5, count($result->builderResult->artifacts));

        $extensions = [];
        foreach ($result->builderResult->diagnostics->all() as $diagnostic) {
            if ($diagnostic->extension !== null) {
                $extensions[$diagnostic->extension] = true;
            }
        }

        self::assertArrayHasKey('generated.artifacts', $extensions);
        self::assertFileExists($this->output . '/engineering/INDEX.generated.md');
        self::assertFileExists($this->output . '/engineering/REFERENCES.generated.md');
        self::assertFileExists($this->output . '/engineering/NAVIGATION.generated.md');
        self::assertFileExists($this->output . '/build/reference-graph.generated.json');
        self::assertFileExists($this->output . '/build/repository-manifest.generated.json');
    }

    public function testStrictBuildSuppressesGenerationWhenReferenceIntegrityReportsAnError(): void
    {
        file_put_contents(
            $this->repository . '/engineering/work-packages/WP-106.md',
            $this->document(
                id: 'WP-106',
                title: 'Built-in analyzers',
                category: 'Work Package',
                documentClass: 'GovernanceDocument',
                related: ['ADR-001', 'ADR-999'],
                workPackage: 'WP-106',
            ),
        );

        $application = (new DefaultCliApplicationFactory($this->sandbox))->create();
        $result = $application->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--strict',
        ]));

        self::assertSame(5, $result->exitCode->value, $result->standardError ?? '');
        self::assertNotNull($result->builderResult);

        $codes = array_map(
            static fn ($diagnostic): string => $diagnostic->code,
            $result->builderResult->diagnostics->all(),
        );

        self::assertContains('REFINT-201', $codes);
        self::assertFileDoesNotExist($this->output . '/engineering/INDEX.generated.md');
        self::assertSame(0, count($result->builderResult->artifacts));
    }

    /** @param list<string> $related */
    private function document(
        string $id,
        string $title,
        string $category,
        string $documentClass,
        array $related,
        string $workPackage,
    ): string {
        $references = $related === [] ? '[]' : '[' . implode(', ', $related) . ']';

        return <<<MD
---
id: {$id}
title: "{$title}"
summary: "End-to-end fixture for {$title}."
status: Approved
version: 1.0.0
category: "{$category}"
document_class: {$documentClass}
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder, analyzer]
depends_on: []
related_adrs: {$references}
work_package: {$workPackage}
---

# {$title}
MD;
    }

    private function remove(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . '/' . $item;
            is_dir($target) ? $this->remove($target) : @unlink($target);
        }

        @rmdir($path);
    }
}
