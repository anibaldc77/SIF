<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Reporting;

use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Contract\ReporterInterface;

final readonly class MarkdownBuilderReporter implements ReporterInterface
{
    public function id(): string
    {
        return 'report.markdown';
    }

    public function mediaType(): string
    {
        return 'text/markdown';
    }

    public function render(BuilderResult $result): string
    {
        $lines = [
            '# SIF Builder Execution Report',
            '',
            '## Summary',
            '',
            sprintf('- Status: `%s`', $result->status->value),
            sprintf('- Run: `%s`', $result->runIdentifier ?? 'not-recorded'),
            sprintf('- Completed phases: %d', $result->statistics->completedPhaseCount),
            sprintf('- Diagnostics: %d', $result->statistics->diagnosticCount),
            sprintf('- Artifacts: %d', $result->statistics->artifactCount),
        ];

        if ($result->failureSummary !== null) {
            $lines[] = sprintf('- Failure: %s', $this->escape($result->failureSummary));
        }

        $lines[] = '';
        $lines[] = '## Completed phases';
        $lines[] = '';
        foreach ($result->completedPhases as $phase) {
            $lines[] = sprintf('- `%s`', $phase->value);
        }
        if ($result->completedPhases === []) {
            $lines[] = '- None';
        }

        $lines[] = '';
        $lines[] = '## Diagnostics';
        $lines[] = '';
        $lines[] = '| Severity | Code | Source | Extension | Message |';
        $lines[] = '|---|---|---|---|---|';
        foreach ($result->diagnostics as $diagnostic) {
            $lines[] = sprintf(
                '| %s | `%s` | %s | %s | %s |',
                strtoupper($diagnostic->severity->label()),
                $diagnostic->code,
                $this->escape($diagnostic->source ?? ''),
                $this->escape($diagnostic->extension ?? ''),
                $this->escape($diagnostic->message),
            );
        }
        if ($result->diagnostics->isEmpty()) {
            $lines[] = '| — | — | — | — | No diagnostics |';
        }

        $lines[] = '';
        $lines[] = '## Artifacts';
        $lines[] = '';
        $lines[] = '| Path | Type | Generator | SHA-256 |';
        $lines[] = '|---|---|---|---|';
        foreach ($result->artifacts as $artifact) {
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` | `%s` |',
                $this->escape($artifact->relativePath),
                $artifact->type,
                $artifact->generator,
                $artifact->checksum(),
            );
        }
        if (count($result->artifacts) === 0) {
            $lines[] = '| — | — | — | No artifacts |';
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function escape(string $value): string
    {
        return str_replace(['|', "\r", "\n"], ['\\|', ' ', ' '], $value);
    }
}
