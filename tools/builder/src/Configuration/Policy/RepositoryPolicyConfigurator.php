<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy;

use InvalidArgumentException;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;
use Sif\Builder\Configuration\ConfigurationDiagnostic;
use Sif\Builder\Configuration\RepositoryConfiguration;

final readonly class RepositoryPolicyConfigurator
{
    public function __construct(
        private RepositoryPolicyFactoryCatalog $catalog = new RepositoryPolicyFactoryCatalog(),
    ) {
    }

    public static function withBuiltInFactories(): self
    {
        return new self(RepositoryPolicyFactoryCatalog::builtIn());
    }

    public function configure(RepositoryConfiguration $configuration): RepositoryPolicyConfigurationResult
    {
        $rules = [];
        $diagnostics = [];
        $seenRuleIds = [];

        foreach ($configuration->repositoryPolicies as $factoryId => $entries) {
            if (!$this->catalog->has($factoryId)) {
                $diagnostics[] = $this->diagnostic(
                    $configuration,
                    sprintf('Repository policy type "%s" is not registered.', $factoryId),
                    ['policy_type' => $factoryId],
                );
                continue;
            }

            foreach ($entries as $index => $entry) {
                try {
                    $rule = $this->catalog->get($factoryId)->create($entry);
                    if (isset($seenRuleIds[$rule->id()])) {
                        throw new InvalidArgumentException(sprintf('Repository policy rule "%s" is declared more than once.', $rule->id()));
                    }
                    $seenRuleIds[$rule->id()] = true;
                    $rules[] = $rule;
                } catch (InvalidArgumentException $exception) {
                    $diagnostics[] = $this->diagnostic(
                        $configuration,
                        $exception->getMessage(),
                        ['policy_type' => $factoryId, 'entry' => $index],
                    );
                }
            }
        }

        if ($diagnostics !== []) {
            return new RepositoryPolicyConfigurationResult(null, $diagnostics);
        }

        return new RepositoryPolicyConfigurationResult(new RepositoryPolicySet($rules));
    }

    /** @param array<string, scalar|null> $context */
    private function diagnostic(RepositoryConfiguration $configuration, string $message, array $context): ConfigurationDiagnostic
    {
        return new ConfigurationDiagnostic(
            code: 'CONFIG-112',
            message: $message,
            path: $configuration->sourcePath,
            context: $context,
        );
    }
}
