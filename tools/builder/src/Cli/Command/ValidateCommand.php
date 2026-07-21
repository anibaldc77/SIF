<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Command;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\BuilderCommandResultFactoryInterface;
use Sif\Builder\Cli\Contract\BuilderEngineFactoryInterface;
use Sif\Builder\Cli\Contract\BuilderRequestFactoryInterface;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Exception\RequestMappingException;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Reporting\BuilderCommandResultFactory;
use Sif\Builder\Cli\Reporting\ExecutionCommandType;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;

final readonly class ValidateCommand implements CommandInterface
{
    private BuilderCommandResultFactoryInterface $results;

    public function __construct(
        private BuilderRequestFactoryInterface $requests,
        private BuilderEngineFactoryInterface $engines,
        ?BuilderCommandResultFactoryInterface $results = null,
    ) {
        $this->results = $results ?? new BuilderCommandResultFactory();
    }

    public function name(): string
    {
        return 'validate';
    }

    public function description(): string
    {
        return 'Analyze a repository without persisting generated artifacts.';
    }

    public function execute(CommandInput $input): CommandResult
    {
        try {
            $request = $this->requests->create($input, EngineExecutionMode::ANALYSIS_ONLY);
        } catch (RequestMappingException $exception) {
            return CommandResult::failure(ExitCode::INVALID_USAGE, $exception->getMessage());
        }

        $builderResult = $this->engines->create(EngineExecutionMode::ANALYSIS_ONLY)->run($request);

        return $this->results->create(ExecutionCommandType::VALIDATE, $input, $builderResult);
    }
}
