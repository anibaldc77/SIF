<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Contract\CliApplicationInterface;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class BuildProfilesEndToEndTest extends TestCase
{
    private string $sandbox;
    private string $repository;
    private string $output;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir()
            . '/sif-builder-wp107-'
            . bin2hex(random_bytes(6));

        $this->repository = $this->sandbox . '/repository';
        $this->output = $this->sandbox . '/output';

        mkdir(
            $this->repository . '/engineering/decisions',
            0777,
            true,
        );

        mkdir(
            $this->repository . '/engineering/work-packages',
            0777,
            true,
        );

        file_put_contents(
            $this->repository . '/engineering/decisions/ADR-001.md',
            $this->document(
                id: 'ADR-001',
                title: 'Build profile architecture',
                category: 'Architecture Decision Record',
                documentClass: 'GovernanceDocument',
                related: [],
            ),
        );

        file_put_contents(
            $this->repository . '/engineering/work-packages/WP-107.md',
            $this->document(
                id: 'WP-107',
                title: 'Build profiles',
                category: 'Work Package',
                documentClass: 'GovernanceDocument',
                related: ['ADR-001'],
            ),
        );
    }

    protected function tearDown(): void
    {
        $this->remove($this->sandbox);
    }

    public function testMissingConfigurationPreservesTheBuiltInDefaultBuild(): void
    {
        $result = $this->application()->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--format=json',
        ]));

        self::assertSame(
            ExitCode::SUCCESS->value,
            $result->exitCode->value,
            $result->standardError ?? '',
        );

        self::assertNotNull($result->builderResult);
        self::assertCount(5, $result->builderResult->artifacts);

        self::assertFileExists(
            $this->output . '/engineering/INDEX.generated.md',
        );

        self::assertFileExists(
            $this->output . '/engineering/REFERENCES.generated.md',
        );

        self::assertFileExists(
            $this->output . '/engineering/NAVIGATION.generated.md',
        );

        self::assertFileExists(
            $this->output . '/build/reference-graph.generated.json',
        );

        self::assertFileExists(
            $this->output . '/build/repository-manifest.generated.json',
        );
    }

    public function testInheritedProfileAndConfiguredPolicyAreAppliedByTheProductionCli(): void
    {
        $this->writeConfiguration([
            'schema_version' => '1.0',
            'default_profile' => 'development',
            'repository_policies' => [
                'required.category' => [[
                    'id' => 'repository.security',
                    'category' => 'Security Standard',
                    'severity' => 'error',
                ]],
            ],
            'profiles' => [
                'base' => [
                    'analyzers' => ['repository.policy'],
                    'generators' => ['repository.index'],
                    'reporters' => ['report.json'],
                    'execution' => [
                        'strict' => false,
                    ],
                ],
                'development' => [
                    'extends' => 'base',
                    'execution' => [
                        'strict' => true,
                    ],
                ],
            ],
        ]);

        $result = $this->application()->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
        ]));

        self::assertSame(
            ExitCode::GENERATION_FAILED->value,
            $result->exitCode->value,
            $result->standardError ?? '',
        );

        self::assertNotNull($result->builderResult);

        $diagnostics = $result->builderResult->diagnostics->all();

        $policyDiagnostics = array_values(array_filter(
            $diagnostics,
            static fn ($diagnostic): bool =>
                $diagnostic->code === 'REPPOL-201',
        ));

        self::assertCount(1, $policyDiagnostics);

        self::assertSame(
            'repository.policy',
            $policyDiagnostics[0]->extension,
        );

        self::assertSame(
            'repository.security',
            $policyDiagnostics[0]->context['rule_id'] ?? null,
        );

        self::assertCount(
            0,
            $result->builderResult->artifacts,
        );

        self::assertFileDoesNotExist(
            $this->output . '/engineering/INDEX.generated.md',
        );
    }

    public function testExplicitCliSelectionsReplaceProfileValues(): void
    {
        $this->writeConfiguration([
            'schema_version' => '1.0',
            'default_profile' => 'analysis-only',
            'profiles' => [
                'analysis-only' => [
                    'analyzers' => [],
                    'generators' => [],
                    'reporters' => ['report.json'],
                    'execution' => [
                        'strict' => true,
                    ],
                ],
            ],
        ]);

        $result = $this->application()->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
            '--generator=repository.index',
            '--lenient',
            '--format=json',
        ]));

        self::assertSame(
            ExitCode::SUCCESS->value,
            $result->exitCode->value,
            $result->standardError ?? '',
        );

        self::assertNotNull($result->builderResult);
        self::assertCount(1, $result->builderResult->artifacts);

        self::assertFileExists(
            $this->output . '/engineering/INDEX.generated.md',
        );

        self::assertFileDoesNotExist(
            $this->output . '/engineering/REFERENCES.generated.md',
        );

        self::assertFileDoesNotExist(
            $this->output . '/build/repository-manifest.generated.json',
        );
    }

    public function testUnknownConfiguredGeneratorFailsBeforeEngineExecution(): void
    {
        $this->writeConfiguration([
            'schema_version' => '1.0',
            'default_profile' => 'invalid',
            'profiles' => [
                'invalid' => [
                    'analyzers' => [],
                    'generators' => ['repository.unknown'],
                    'reporters' => ['report.json'],
                    'execution' => [
                        'strict' => false,
                    ],
                ],
            ],
        ]);

        $result = $this->application()->run(new ArgvInput([
            'build',
            '--repository=' . $this->repository,
            '--output=' . $this->output,
        ]));

        self::assertSame(
            ExitCode::INVALID_USAGE->value,
            $result->exitCode->value,
        );

        self::assertStringContainsString(
            'CONFIG-110',
            $result->standardError ?? '',
        );

        self::assertNull($result->builderResult);
        self::assertDirectoryDoesNotExist($this->output);
    }

    private function application(): CliApplicationInterface
    {
        return (new DefaultCliApplicationFactory(
            $this->sandbox,
        ))->create();
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function writeConfiguration(array $configuration): void
    {
        mkdir(
            $this->repository . '/.sif',
            0777,
            true,
        );

        file_put_contents(
            $this->repository . '/.sif/builder.json',
            json_encode(
                $configuration,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
            ),
        );
    }

    /**
     * @param list<string> $related
     */
    private function document(
        string $id,
        string $title,
        string $category,
        string $documentClass,
        array $related,
    ): string {
        $references = $related === []
            ? '[]'
            : '[' . implode(', ', $related) . ']';

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
tags: [builder, configuration]
depends_on: []
related_adrs: {$references}
work_package: WP-107
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

            is_dir($target)
                ? $this->remove($target)
                : @unlink($target);
        }

        @rmdir($path);
    }
}