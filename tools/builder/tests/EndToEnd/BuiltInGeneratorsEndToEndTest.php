<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class BuiltInGeneratorsEndToEndTest extends TestCase
{
    private string $sandbox;
    private string $repository;
    private string $output;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/sif-builder-wp105-' . bin2hex(random_bytes(6));
        $this->repository = $this->sandbox . '/repository';
        $this->output = $this->sandbox . '/output';
        mkdir($this->repository . '/engineering/decisions', 0777, true);
        mkdir($this->repository . '/engineering/work-packages', 0777, true);

        file_put_contents($this->repository . '/engineering/decisions/ADR-001.md', $this->document(
            'ADR-001', 'Builder architecture', 'Architecture Decision Record', 'GovernanceDocument', []
        ));
        file_put_contents($this->repository . '/engineering/work-packages/WP-105.md', $this->document(
            'WP-105', 'Built-in generators', 'Work Package', 'GovernanceDocument', ['ADR-001']
        ));
    }

    protected function tearDown(): void
    {
        $this->remove($this->sandbox);
    }

    public function testBuildProducesAllGovernedArtifacts(): void
    {
        $application = (new DefaultCliApplicationFactory($this->sandbox))->create();
        $result = $application->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--format=json',
            '--strict',
        ]));

        self::assertSame(0, $result->exitCode->value, (string) $result->standardError);
        self::assertNotNull($result->builderResult);
        self::assertSame(5, count($result->builderResult->artifacts));

        foreach ([
            'engineering/INDEX.generated.md',
            'engineering/REFERENCES.generated.md',
            'engineering/NAVIGATION.generated.md',
            'build/reference-graph.generated.json',
            'build/repository-manifest.generated.json',
        ] as $relativePath) {
            self::assertFileExists($this->output . '/' . $relativePath);
            self::assertNotSame('', file_get_contents($this->output . '/' . $relativePath));
        }

        $manifest = json_decode((string) file_get_contents(
            $this->output . '/build/repository-manifest.generated.json'
        ), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(2, $manifest['summary']['documents']);
    }

    public function testGeneratorSelectionWritesOnlyRequestedArtifact(): void
    {
        $application = (new DefaultCliApplicationFactory($this->sandbox))->create();
        $result = $application->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--generator=repository.index',
            '--strict',
        ]));

        self::assertSame(0, $result->exitCode->value, (string) $result->standardError);
        self::assertFileExists($this->output . '/engineering/INDEX.generated.md');
        self::assertFileDoesNotExist($this->output . '/engineering/REFERENCES.generated.md');
        self::assertSame(1, count($result->builderResult?->artifacts ?? []));
    }

    /** @param list<string> $related */
    private function document(string $id, string $title, string $category, string $class, array $related): string
    {
        $references = $related === [] ? '[]' : '[' . implode(', ', $related) . ']';

        return <<<MD
---
id: {$id}
title: "{$title}"
status: Approved
version: 1.0.0
category: "{$category}"
document_class: {$class}
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder]
depends_on: []
related_adrs: {$references}
work_package: WP-105
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
