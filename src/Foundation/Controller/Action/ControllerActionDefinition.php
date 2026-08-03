<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Action;

use Sif\Foundation\Controller\Argument\ActionArgumentDefinition;
use Sif\Foundation\Controller\Exceptions\ControllerActionException;

final readonly class ControllerActionDefinition
{
    /** @var list<ActionArgumentDefinition> */
    private array $arguments;

    /** @param list<ActionArgumentDefinition> $arguments */
    public function __construct(
        private string $identifier,
        private string $controllerIdentifier,
        private string $method,
        array $arguments = [],
    ) {
        self::assertIdentifier($identifier, 'action');
        self::assertIdentifier($controllerIdentifier, 'controller');

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $method) !== 1) {
            throw new ControllerActionException(sprintf('Invalid controller action method "%s".', $method));
        }

        $seen = [];
        foreach ($arguments as $argument) {
            if (isset($seen[$argument->name()])) {
                throw new ControllerActionException(sprintf(
                    'Controller action "%s" declares duplicate argument "%s".',
                    $identifier,
                    $argument->name(),
                ));
            }
            $seen[$argument->name()] = true;
        }

        $this->arguments = array_values($arguments);
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function controllerIdentifier(): string
    {
        return $this->controllerIdentifier;
    }

    public function method(): string
    {
        return $this->method;
    }

    /** @return list<ActionArgumentDefinition> */
    public function arguments(): array
    {
        return $this->arguments;
    }

    private static function assertIdentifier(string $identifier, string $kind): void
    {
        if (
            $identifier === ''
            || trim($identifier) !== $identifier
            || preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1
        ) {
            throw new ControllerActionException(sprintf('Invalid %s identifier "%s".', $kind, $identifier));
        }
    }
}
