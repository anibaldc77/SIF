<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use Sif\Builder\Cli\Contract\PathResolverInterface;
use Sif\Builder\Cli\Exception\RequestMappingException;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Configuration\Extension\BuildProfileExtensionValidator;
use Sif\Builder\Configuration\Extension\ExtensionCatalog;
use Sif\Builder\Configuration\JsonRepositoryConfigurationLoader;
use Sif\Builder\Configuration\Policy\RepositoryPolicyConfigurator;
use Sif\Builder\Configuration\Policy\RepositoryPolicyFactoryCatalog;
use Sif\Builder\Configuration\Profile\BuildProfileResolver;

final readonly class CliRepositoryConfigurationResolver
{
    public function __construct(
        private PathResolverInterface $paths,
        private JsonRepositoryConfigurationLoader $loader = new JsonRepositoryConfigurationLoader(),
        private BuildProfileResolver $profiles = new BuildProfileResolver(),
        private BuildProfileExtensionValidator $extensions = new BuildProfileExtensionValidator(),
        private RepositoryPolicyConfigurator $policies = new RepositoryPolicyConfigurator(
            new RepositoryPolicyFactoryCatalog([
                new \Sif\Builder\Configuration\Policy\Factory\RequiredCategoryPolicyFactory(),
                new \Sif\Builder\Configuration\Policy\Factory\RequiredMetadataPolicyFactory(),
            ]),
        ),
        private ExtensionCatalog $catalog = new ExtensionCatalog(
            analyzers: [
                'metadata.completeness',
                'reference.integrity',
                'document.consistency',
                'repository.policy',
                'generated.artifacts',
            ],
            generators: [
                'repository.index',
                'reference.report',
                'reference.graph',
                'repository.manifest',
                'documentation.navigation',
            ],
            reporters: ['report.markdown', 'report.json'],
        ),
    ) {
    }

    public function resolve(CommandInput $input): ResolvedCliConfiguration
    {
        $repository = $input->option('repository') ?? $input->argument(0) ?? '.';
        $repositoryRoot = $this->paths->resolve($repository);
        $configurationPath = $input->option('configuration');
        if ($configurationPath !== null) {
            $configurationPath = $this->paths->resolve($configurationPath);
        }

        $loaded = $this->loader->load($repositoryRoot, $configurationPath);
        if (!$loaded->isSuccessful() || $loaded->configuration === null) {
            throw new RequestMappingException($this->message($loaded->diagnostics));
        }

        $resolved = $this->profiles->resolve($loaded->configuration, $input->option('profile'));
        if (!$resolved->isSuccessful() || $resolved->profile === null) {
            throw new RequestMappingException($this->message($resolved->diagnostics));
        }

        $validated = $this->extensions->validate(
            $resolved->profile,
            $this->catalog,
            $loaded->configuration->sourcePath,
        );
        if (!$validated->isSuccessful() || $validated->profile === null) {
            throw new RequestMappingException($this->message($validated->diagnostics));
        }

        $configuredPolicies = $this->policies->configure($loaded->configuration);
        if (!$configuredPolicies->isSuccessful() || $configuredPolicies->policies === null) {
            throw new RequestMappingException($this->message($configuredPolicies->diagnostics));
        }

        return new ResolvedCliConfiguration(
            $validated->profile,
            $configuredPolicies->policies,
            $loaded->configuration->sourcePath,
        );
    }

    /** @param list<object> $diagnostics */
    private function message(array $diagnostics): string
    {
        $parts = [];
        foreach ($diagnostics as $diagnostic) {
            /** @var object{code:string,message:string} $diagnostic */
            $parts[] = sprintf('%s: %s', $diagnostic->code, $diagnostic->message);
        }

        return $parts === [] ? 'Repository configuration is invalid.' : implode(PHP_EOL, $parts);
    }
}
