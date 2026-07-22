<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Extension;

use Sif\Builder\Configuration\ConfigurationDiagnostic;
use Sif\Builder\Configuration\Profile\ResolvedBuildProfile;

final class BuildProfileExtensionValidator implements BuildProfileExtensionValidatorInterface
{
    public function validate(
        ResolvedBuildProfile $profile,
        ExtensionCatalog $catalog,
        ?string $sourcePath = null,
    ): ExtensionCatalogValidationResult {
        $diagnostics = [];

        foreach ($profile->analyzers as $identifier) {
            if (!$catalog->hasAnalyzer($identifier)) {
                $diagnostics[] = $this->unknown(
                    code: 'CONFIG-109',
                    category: 'analyzer',
                    identifier: $identifier,
                    profile: $profile->identifier,
                    sourcePath: $sourcePath,
                );
            }
        }

        foreach ($profile->generators as $identifier) {
            if (!$catalog->hasGenerator($identifier)) {
                $diagnostics[] = $this->unknown(
                    code: 'CONFIG-110',
                    category: 'generator',
                    identifier: $identifier,
                    profile: $profile->identifier,
                    sourcePath: $sourcePath,
                );
            }
        }

        foreach ($profile->reporters as $identifier) {
            if (!$catalog->hasReporter($identifier)) {
                $diagnostics[] = $this->unknown(
                    code: 'CONFIG-111',
                    category: 'reporter',
                    identifier: $identifier,
                    profile: $profile->identifier,
                    sourcePath: $sourcePath,
                );
            }
        }

        if ($diagnostics !== []) {
            return new ExtensionCatalogValidationResult(null, $diagnostics);
        }

        return new ExtensionCatalogValidationResult($profile);
    }

    private function unknown(
        string $code,
        string $category,
        string $identifier,
        string $profile,
        ?string $sourcePath,
    ): ConfigurationDiagnostic {
        return new ConfigurationDiagnostic(
            $code,
            sprintf(
                'Build profile "%s" declares unknown %s "%s".',
                $profile,
                $category,
                $identifier,
            ),
            $sourcePath,
            [
                'profile' => $profile,
                'category' => $category,
                'extension' => $identifier,
            ],
        );
    }
}
