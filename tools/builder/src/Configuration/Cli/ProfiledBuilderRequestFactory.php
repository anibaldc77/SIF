<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use Sif\Builder\Cli\Configuration\BuilderRequestFactory;
use Sif\Builder\Cli\Contract\BuilderRequestFactoryInterface;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\BuilderRequest;

final readonly class ProfiledBuilderRequestFactory implements BuilderRequestFactoryInterface
{
    public function __construct(
        private BuilderRequestFactory $delegate,
        private CliRepositoryConfigurationResolver $resolver,
        private ResolvedCliConfigurationStore $store,
    ) {
    }

    public function create(CommandInput $input, EngineExecutionMode $mode): BuilderRequest
    {
        $configuration = $this->resolver->resolve($input);
        $this->store->replace($configuration);
        $profile = $configuration->profile;

        $options = $input->options;
        unset($options['profile'], $options['configuration']);

        if (!isset($options['analyzer'])) {
            $options['analyzer'] = $profile->analyzers;
        }
        if (!isset($options['generator'])) {
            $options['generator'] = $profile->generators;
        }
        if (!isset($options['format']) && $profile->reporters !== []) {
            $options['format'] = [$profile->reporters[0]];
        }
        if (!isset($options['policy'])
            && !$input->hasFlag('strict')
            && !$input->hasFlag('lenient')
        ) {
            $options['policy'] = [$profile->strict ? 'strict' : 'lenient'];
        }

        $options = array_filter($options, static fn (array $values): bool => $values !== []);

        return $this->delegate->create(new CommandInput(
            commandName: $input->commandName,
            arguments: $input->arguments,
            options: $options,
            flags: $input->flags,
        ), $mode);
    }
}
