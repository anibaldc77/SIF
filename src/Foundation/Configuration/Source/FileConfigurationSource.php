<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source;

use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnosticSeverity;
use Sif\Foundation\Configuration\Exceptions\ConfigurationSourceNotFoundException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;
use Sif\Foundation\Configuration\Loader\ConfigurationFileLoader;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;

final readonly class FileConfigurationSource implements ConfigurationSourceInterface
{
    public function __construct(
        private string $sourceId,
        private string $path,
        private int $sourcePrecedence = 0,
        private bool $required = true,
        private ?ConfigurationFileLoader $loader = null,
    ) {
        if (trim($sourceId) === '' || trim($path) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyIdentifier();
        }
    }

    public function id(): string { return $this->sourceId; }
    public function type(): string { return 'file'; }
    public function precedence(): int { return $this->sourcePrecedence; }

    public function load(): ConfigurationSourceResult
    {
        try {
            $values = ($this->loader ?? ConfigurationFileLoader::withDefaultLoaders())->load($this->path);
        } catch (ConfigurationSourceNotFoundException $cause) {
            if ($this->required) {
                throw $cause;
            }

            return new ConfigurationSourceResult(
                $this->sourceId,
                $this->type(),
                $this->sourcePrecedence,
                [],
                diagnostics: [new ConfigurationDiagnostic(
                    'CFG_SOURCE_OPTIONAL_FILE_MISSING',
                    ConfigurationDiagnosticSeverity::Warning,
                    'Optional configuration file was not found; the source contributed no values.',
                    $this->sourceId,
                    ['path' => $this->path],
                )],
            );
        }

        return new ConfigurationSourceResult(
            $this->sourceId,
            $this->type(),
            $this->sourcePrecedence,
            $values,
            diagnostics: [new ConfigurationDiagnostic(
                'CFG_SOURCE_FILE_LOADED',
                ConfigurationDiagnosticSeverity::Info,
                'Configuration file loaded successfully.',
                $this->sourceId,
                ['path' => $this->path],
            )],
        );
    }
}
