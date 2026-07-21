<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Configuration;

use Sif\Builder\Cli\Contract\BuilderRequestFactoryInterface;
use Sif\Builder\Cli\Contract\PathResolverInterface;
use Sif\Builder\Cli\Exception\RequestMappingException;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\ExecutionPolicy;
use Throwable;

final readonly class BuilderRequestFactory implements BuilderRequestFactoryInterface
{
    public function __construct(private PathResolverInterface $paths)
    {
    }

    public function create(CommandInput $input, EngineExecutionMode $mode): BuilderRequest
    {
        $this->assertSupportedInput($input);

        $repository = $input->option('repository') ?? $input->argument(0) ?? '.';
        $output = $input->option('output');
        $policy = $this->policy($input);

        if ($mode === EngineExecutionMode::ANALYSIS_ONLY) {
            $output = null;
        }

        try {
            return new BuilderRequest(
                repositoryRoot: $this->paths->resolve($repository),
                outputRoot: $output === null ? null : $this->paths->resolve($output),
                policy: $policy,
                enabledAnalyzers: $input->optionValues('analyzer'),
                enabledGenerators: $mode === EngineExecutionMode::ANALYSIS_ONLY
                    ? []
                    : $input->optionValues('generator'),
            );
        } catch (Throwable $throwable) {
            throw new RequestMappingException($throwable->getMessage(), 0, $throwable);
        }
    }

    private function policy(CommandInput $input): ExecutionPolicy
    {
        $value = $input->option('policy');
        if ($input->hasFlag('strict')) {
            $value = 'strict';
        } elseif ($input->hasFlag('lenient')) {
            $value = 'lenient';
        }

        return match ($value ?? 'strict') {
            'strict' => ExecutionPolicy::STRICT,
            'lenient' => ExecutionPolicy::LENIENT,
            default => throw new RequestMappingException(sprintf('Unsupported execution policy "%s".', $value)),
        };
    }

    private function assertSupportedInput(CommandInput $input): void
    {
        if (count($input->arguments) > 1) {
            throw new RequestMappingException('Only one repository-root positional argument is allowed.');
        }

        $allowedOptions = ['repository', 'output', 'policy', 'analyzer', 'generator', 'format'];
        foreach (array_keys($input->options) as $option) {
            if (!in_array($option, $allowedOptions, true)) {
                throw new RequestMappingException(sprintf('Option "--%s" is not supported by this command.', $option));
            }
        }

        $allowedFlags = ['no-write', 'quiet', 'verbose', 'strict', 'lenient'];
        foreach ($input->flags as $flag) {
            if (!in_array($flag, $allowedFlags, true)) {
                throw new RequestMappingException(sprintf('Flag "--%s" is not supported by this command.', $flag));
            }
        }
    }
}
