<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Contracts\ContainerValidatorInterface;
use Sif\Foundation\Contracts\ServiceDefinitionRegistryInterface;

final readonly class ContainerDefinitionValidator implements
    ContainerValidatorInterface
{
    public function __construct(
        private ServiceDefinitionRegistryInterface $definitions,
        private ContextualBindingRegistry $contextualBindings,
    ) {
    }

    public function validate(): ContainerValidationReport
    {
        $diagnostics = [
            ...$this->validateAliases(),
            ...$this->validateAutowiredClasses(),
            ...$this->validateContextualBindings(),
        ];

        usort(
            $diagnostics,
            static function (
                ContainerDiagnostic $left,
                ContainerDiagnostic $right,
            ): int {
                $code = $left->code()->value
                    <=> $right->code()->value;

                if ($code !== 0) {
                    return $code;
                }

                return $left->message() <=> $right->message();
            },
        );

        return new ContainerValidationReport($diagnostics);
    }

    /**
     * @return list<ContainerDiagnostic>
     */
    private function validateAliases(): array
    {
        $diagnostics = [];

        foreach ($this->definitions->all() as $definition) {
            if (!$definition->isAlias()) {
                continue;
            }

            $target = $definition->aliasTarget();

            if ($target === null || !$this->definitions->has($target)) {
                $diagnostics[] = new ContainerDiagnostic(
                    code: ContainerDiagnosticCode::AliasTargetMissing,
                    severity: ContainerDiagnosticSeverity::Error,
                    message: sprintf(
                        'Alias "%s" targets an unregistered service.',
                        $definition->identifier()->value(),
                    ),
                    context: [
                        'service' => $definition->identifier()->value(),
                        'target' => $target?->value(),
                    ],
                );

                continue;
            }

            $cycle = $this->findAliasCycle($definition->identifier());

            if ($cycle !== null) {
                $diagnostics[] = new ContainerDiagnostic(
                    code: ContainerDiagnosticCode::AliasCycle,
                    severity: ContainerDiagnosticSeverity::Error,
                    message: sprintf(
                        'Alias cycle detected: %s.',
                        $cycle->format(),
                    ),
                    context: [
                        'service' => $definition->identifier()->value(),
                        'path' => $cycle->format(),
                    ],
                );
            }
        }

        return $diagnostics;
    }

    /**
     * @return list<ContainerDiagnostic>
     */
    private function validateAutowiredClasses(): array
    {
        $diagnostics = [];

        foreach ($this->definitions->all() as $definition) {
            if (!$definition->autowire()) {
                continue;
            }

            $className = $definition->className();

            if ($className === null || !class_exists($className)) {
                $diagnostics[] = new ContainerDiagnostic(
                    code: ContainerDiagnosticCode::AutowiredClassMissing,
                    severity: ContainerDiagnosticSeverity::Error,
                    message: sprintf(
                        'Autowired service "%s" references an unavailable class.',
                        $definition->identifier()->value(),
                    ),
                    context: [
                        'service' => $definition->identifier()->value(),
                        'class' => $className,
                    ],
                );
            }
        }

        return $diagnostics;
    }

    /**
     * @return list<ContainerDiagnostic>
     */
    private function validateContextualBindings(): array
    {
        $diagnostics = [];

        foreach ($this->contextualBindings->all() as $contextual) {
            if (!$this->definitions->has($contextual->consumer())) {
                $diagnostics[] = new ContainerDiagnostic(
                    code: ContainerDiagnosticCode::ContextualConsumerMissing,
                    severity: ContainerDiagnosticSeverity::Error,
                    message: sprintf(
                        'Contextual binding consumer "%s" is not registered.',
                        $contextual->consumer()->value(),
                    ),
                    context: [
                        'consumer' => $contextual->consumer()->value(),
                        'parameter' => $contextual->parameterName(),
                    ],
                );
            }

            $binding = $contextual->binding();

            if (
                $binding->kind() === ConstructorBindingKind::Service
                && (
                    $binding->serviceIdentifier() === null
                    || !$this->definitions->has(
                        $binding->serviceIdentifier(),
                    )
                )
            ) {
                $diagnostics[] = new ContainerDiagnostic(
                    code: ContainerDiagnosticCode::ContextualServiceMissing,
                    severity: ContainerDiagnosticSeverity::Error,
                    message: sprintf(
                        'Contextual binding for "%s::%s" references an unregistered service.',
                        $contextual->consumer()->value(),
                        $contextual->parameterName(),
                    ),
                    context: [
                        'consumer' => $contextual->consumer()->value(),
                        'parameter' => $contextual->parameterName(),
                        'service' => $binding
                            ->serviceIdentifier()
                            ?->value(),
                    ],
                );
            }
        }

        return $diagnostics;
    }

    private function findAliasCycle(
        ServiceIdentifier $start,
    ): ?ResolutionPath {
        $visited = [];
        $current = $start;

        while ($this->definitions->has($current)) {
            $value = $current->value();

            if (isset($visited[$value])) {
                return new ResolutionPath([
                    ...array_values($visited),
                    $current,
                ]);
            }

            $visited[$value] = $current;
            $definition = $this->definitions->get($current);

            if (!$definition->isAlias()) {
                return null;
            }

            $target = $definition->aliasTarget();

            if ($target === null) {
                return null;
            }

            $current = $target;
        }

        return null;
    }
}
