<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

use Sif\Foundation\Controller\Exceptions\ControllerArgumentException;

final readonly class ActionArgumentDefinition
{
    public function __construct(
        private string $name,
        private ActionArgumentSource $source,
        private ActionArgumentType $type = ActionArgumentType::Mixed,
        private ?string $sourceKey = null,
        private bool $required = true,
        private bool $nullable = false,
        private bool $hasDefault = false,
        private mixed $defaultValue = null,
        private bool $sensitive = false,
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new ControllerArgumentException(sprintf('Invalid action argument name "%s".', $name));
        }

        if ($this->requiresSourceKey() && $this->resolvedSourceKey() === '') {
            throw new ControllerArgumentException(sprintf('Argument "%s" requires a non-empty source key.', $name));
        }

        if (!$this->requiresSourceKey() && $sourceKey !== null) {
            throw new ControllerArgumentException(sprintf('Argument "%s" cannot define a source key for source "%s".', $name, $source->value));
        }

        if ($source === ActionArgumentSource::Request && $type !== ActionArgumentType::Request) {
            throw new ControllerArgumentException('Request-sourced arguments must use the request type.');
        }

        if ($source === ActionArgumentSource::Context && $type !== ActionArgumentType::Context) {
            throw new ControllerArgumentException('Context-sourced arguments must use the context type.');
        }

        if ($source === ActionArgumentSource::Service && $type !== ActionArgumentType::Service) {
            throw new ControllerArgumentException('Service-sourced arguments must use the service type.');
        }

        if ($hasDefault && $required) {
            throw new ControllerArgumentException(sprintf('Argument "%s" cannot be required and define a default value.', $name));
        }

        if ($hasDefault && $defaultValue === null && !$nullable) {
            throw new ControllerArgumentException(sprintf('Argument "%s" cannot use a null default unless it is nullable.', $name));
        }
    }

    public function name(): string { return $this->name; }
    public function source(): ActionArgumentSource { return $this->source; }
    public function type(): ActionArgumentType { return $this->type; }
    public function sourceKey(): ?string { return $this->requiresSourceKey() ? $this->resolvedSourceKey() : null; }
    public function required(): bool { return $this->required; }
    public function nullable(): bool { return $this->nullable; }
    public function hasDefault(): bool { return $this->hasDefault; }
    public function defaultValue(): mixed { return $this->defaultValue; }
    public function sensitive(): bool { return $this->sensitive; }

    private function requiresSourceKey(): bool
    {
        return !in_array($this->source, [ActionArgumentSource::Request, ActionArgumentSource::Context], true);
    }

    private function resolvedSourceKey(): string
    {
        return $this->sourceKey ?? $this->name;
    }
}
