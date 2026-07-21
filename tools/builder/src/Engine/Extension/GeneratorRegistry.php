<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Exception\DuplicateExtensionException;
use Sif\Builder\Engine\Exception\ExtensionRegistryFrozenException;

final class GeneratorRegistry
{
    /** @var array<string, GeneratorInterface> */
    private array $generators = [];

    private bool $frozen = false;

    public function register(GeneratorInterface $generator): void
    {
        $this->assertMutable();
        $identifier = ExtensionIdentifier::normalize($generator->id());

        if (isset($this->generators[$identifier])) {
            throw new DuplicateExtensionException(sprintf(
                'Generator "%s" is already registered.',
                $identifier,
            ));
        }

        $this->generators[$identifier] = $generator;
    }

    public function has(string $identifier): bool
    {
        return isset($this->generators[ExtensionIdentifier::normalize($identifier)]);
    }

    public function get(string $identifier): ?GeneratorInterface
    {
        return $this->generators[ExtensionIdentifier::normalize($identifier)] ?? null;
    }

    /** @return list<GeneratorInterface> */
    public function all(): array
    {
        return array_values($this->generators);
    }

    /** @param list<string> $requestedIdentifiers */
    public function select(array $requestedIdentifiers = []): GeneratorSelection
    {
        if ($requestedIdentifiers === []) {
            return new GeneratorSelection($this->all());
        }

        $selected = [];
        $diagnostics = new DiagnosticCollection();
        $seen = [];

        foreach ($requestedIdentifiers as $requestedIdentifier) {
            $identifier = ExtensionIdentifier::normalize($requestedIdentifier);

            if (isset($seen[$identifier])) {
                continue;
            }

            $seen[$identifier] = true;
            $generator = $this->generators[$identifier] ?? null;

            if ($generator === null) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'CONFIG-102',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Requested generator "%s" is not registered.', $identifier),
                    extension: $identifier,
                    remediation: 'Register the generator or remove it from the execution request.',
                ));
                continue;
            }

            $selected[] = $generator;
        }

        return new GeneratorSelection($selected, $diagnostics);
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    private function assertMutable(): void
    {
        if ($this->frozen) {
            throw new ExtensionRegistryFrozenException('Generator registry is frozen.');
        }
    }
}
