<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Exception\DuplicateExtensionException;
use Sif\Builder\Engine\Exception\ExtensionRegistryFrozenException;

final class AnalyzerRegistry
{
    /** @var array<string, AnalyzerInterface> */
    private array $analyzers = [];

    private bool $frozen = false;

    public function register(AnalyzerInterface $analyzer): void
    {
        $this->assertMutable();
        $identifier = ExtensionIdentifier::normalize($analyzer->id());

        if (isset($this->analyzers[$identifier])) {
            throw new DuplicateExtensionException(sprintf(
                'Analyzer "%s" is already registered.',
                $identifier,
            ));
        }

        $this->analyzers[$identifier] = $analyzer;
    }

    public function has(string $identifier): bool
    {
        return isset($this->analyzers[ExtensionIdentifier::normalize($identifier)]);
    }

    public function get(string $identifier): ?AnalyzerInterface
    {
        return $this->analyzers[ExtensionIdentifier::normalize($identifier)] ?? null;
    }

    /** @return list<AnalyzerInterface> */
    public function all(): array
    {
        return array_values($this->analyzers);
    }

    /** @param list<string> $requestedIdentifiers */
    public function select(array $requestedIdentifiers = []): AnalyzerSelection
    {
        if ($requestedIdentifiers === []) {
            return new AnalyzerSelection($this->all());
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
            $analyzer = $this->analyzers[$identifier] ?? null;

            if ($analyzer === null) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'CONFIG-101',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Requested analyzer "%s" is not registered.', $identifier),
                    extension: $identifier,
                    remediation: 'Register the analyzer or remove it from the execution request.',
                ));
                continue;
            }

            $selected[] = $analyzer;
        }

        return new AnalyzerSelection($selected, $diagnostics);
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
            throw new ExtensionRegistryFrozenException('Analyzer registry is frozen.');
        }
    }
}
