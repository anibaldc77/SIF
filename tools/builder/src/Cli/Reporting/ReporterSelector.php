<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Reporting;

use Sif\Builder\Cli\Contract\ReporterSelectorInterface;
use Sif\Builder\Cli\Exception\ReporterSelectionException;
use Sif\Builder\Engine\Contract\ReporterInterface;
use Sif\Builder\Engine\Reporting\JsonBuilderReporter;
use Sif\Builder\Engine\Reporting\MarkdownBuilderReporter;

final readonly class ReporterSelector implements ReporterSelectorInterface
{
    /** @var array<string, ReporterInterface> */
    private array $reporters;

    /** @param iterable<ReporterInterface> $reporters */
    public function __construct(iterable $reporters = [])
    {
        $resolved = [];
        foreach ($reporters as $reporter) {
            $id = strtolower(trim($reporter->id()));
            if ($id === '' || isset($resolved[$id])) {
                throw new ReporterSelectionException(sprintf('Reporter identifier "%s" is invalid or duplicated.', $id));
            }
            $resolved[$id] = $reporter;
        }

        if ($resolved === []) {
            $resolved = [
                'report.markdown' => new MarkdownBuilderReporter(),
                'report.json' => new JsonBuilderReporter(),
            ];
        }

        $this->reporters = $resolved;
    }

    public function select(?string $format): ReporterInterface
    {
        $normalized = strtolower(trim($format ?? 'markdown'));
        $identifier = match ($normalized) {
            'markdown', 'md', 'report.markdown' => 'report.markdown',
            'json', 'report.json' => 'report.json',
            default => $normalized,
        };

        if (!isset($this->reporters[$identifier])) {
            throw new ReporterSelectionException(sprintf('Reporter format "%s" is not available.', $normalized));
        }

        return $this->reporters[$identifier];
    }
}
