<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Extension;

use Sif\Builder\Configuration\Profile\ResolvedBuildProfile;

interface BuildProfileExtensionValidatorInterface
{
    public function validate(
        ResolvedBuildProfile $profile,
        ExtensionCatalog $catalog,
        ?string $sourcePath = null,
    ): ExtensionCatalogValidationResult;
}
