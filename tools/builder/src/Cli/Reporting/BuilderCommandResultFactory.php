<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Reporting;

use Throwable;
use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\BuilderCommandResultFactoryInterface;
use Sif\Builder\Cli\Contract\BuilderExitCodeMapperInterface;
use Sif\Builder\Cli\Contract\ReporterSelectorInterface;
use Sif\Builder\Cli\Exception\ReporterSelectionException;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Engine\BuilderResult;

final readonly class BuilderCommandResultFactory implements BuilderCommandResultFactoryInterface
{
    public function __construct(
        private ReporterSelectorInterface $reporters = new ReporterSelector(),
        private BuilderExitCodeMapperInterface $exitCodes = new BuilderExitCodeMapper(),
    ) {
    }

    public function create(
        ExecutionCommandType $commandType,
        CommandInput $input,
        BuilderResult $builderResult,
    ): CommandResult {
        try {
            $reporter = $this->reporters->select($input->option('format'));
            $rendered = $reporter->render($builderResult);
        } catch (ReporterSelectionException $exception) {
            return CommandResult::failure(
                ExitCode::CONFIGURATION_ERROR,
                $exception->getMessage(),
                builderResult: $builderResult,
            );
        } catch (Throwable) {
            return CommandResult::failure(
                ExitCode::INTERNAL_ERROR,
                'The execution report could not be rendered.',
                builderResult: $builderResult,
            );
        }

        $exitCode = $this->exitCodes->map($commandType, $builderResult);
        $quiet = $input->hasFlag('quiet');
        $standardOutput = $quiet && $exitCode === ExitCode::SUCCESS ? null : $rendered;

        if ($exitCode === ExitCode::SUCCESS) {
            return CommandResult::success($standardOutput, $builderResult);
        }

        $summary = $builderResult->failureSummary ?? $this->summaryFor($exitCode);

        return CommandResult::failure(
            $exitCode,
            $summary,
            standardError: $summary,
            standardOutput: $standardOutput,
            builderResult: $builderResult,
        );
    }

    private function summaryFor(ExitCode $exitCode): string
    {
        return match ($exitCode) {
            ExitCode::CONFIGURATION_ERROR => 'Builder configuration is invalid.',
            ExitCode::VALIDATION_FAILED => 'Repository validation failed.',
            ExitCode::GENERATION_FAILED => 'Artifact generation failed.',
            ExitCode::PARTIAL_SUCCESS => 'The build completed with reportable errors.',
            default => 'The Builder command failed.',
        };
    }
}
