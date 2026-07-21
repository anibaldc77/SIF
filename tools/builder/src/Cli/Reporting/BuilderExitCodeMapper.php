<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Reporting;

use Sif\Builder\Cli\Contract\BuilderExitCodeMapperInterface;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\BuilderStatus;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final readonly class BuilderExitCodeMapper implements BuilderExitCodeMapperInterface
{
    public function map(ExecutionCommandType $commandType, BuilderResult $result): ExitCode
    {
        if ($this->hasConfigurationFailure($result)) {
            return ExitCode::CONFIGURATION_ERROR;
        }

        if ($result->status === BuilderStatus::FAILED) {
            return $commandType === ExecutionCommandType::VALIDATE
                ? ExitCode::VALIDATION_FAILED
                : ExitCode::GENERATION_FAILED;
        }

        $hasErrors = $result->diagnostics->hasSeverity(DiagnosticSeverity::ERROR)
            || $result->diagnostics->hasSeverity(DiagnosticSeverity::FATAL);

        if (!$hasErrors) {
            return ExitCode::SUCCESS;
        }

        if ($commandType === ExecutionCommandType::VALIDATE) {
            return ExitCode::VALIDATION_FAILED;
        }

        if (count($result->artifacts) > 0) {
            return ExitCode::PARTIAL_SUCCESS;
        }

        return ExitCode::GENERATION_FAILED;
    }

    private function hasConfigurationFailure(BuilderResult $result): bool
    {
        foreach ($result->diagnostics as $diagnostic) {
            if (str_starts_with($diagnostic->code, 'CONFIG-')) {
                return true;
            }
        }

        return false;
    }
}
