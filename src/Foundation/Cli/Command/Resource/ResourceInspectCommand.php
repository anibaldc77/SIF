<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Resource;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Resources\Contracts\ResourceRegistryInterface;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;

final readonly class ResourceInspectCommand implements CliCommandInterface
{
    public function __construct(private ResourceRegistryInterface $registry)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('resource:inspect'),
            'Inspects a registered resource.',
            null,
            [
                new CliArgumentDefinition(new CliArgumentName('namespace'), 'Resource namespace.', true),
                new CliArgumentDefinition(new CliArgumentName('identifier'), 'Resource identifier.', true),
            ],
            [],
            CliOperationalClass::inspection(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $namespace = new ResourceNamespace((string) $invocation->argument(0));
        $identifier = new ResourceIdentifier((string) $invocation->argument(1));

        if (!$this->registry->has($namespace, $identifier)) {
            return new CliCommandResult(
                CliExitCode::validationFailure(),
                'Resource is not registered.',
                ['qualified_identifier' => $namespace->value() . ':' . $identifier->value()],
            );
        }

        $descriptor = $this->registry->get($namespace, $identifier);

        return new CliCommandResult(
            CliExitCode::success(),
            'Resource details',
            ['resource' => $descriptor->summary()],
        );
    }
}
