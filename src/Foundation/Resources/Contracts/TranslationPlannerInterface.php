<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Localization\ImmutableTranslationPlan;
use Sif\Foundation\Resources\Localization\LocaleIdentifier;
use Sif\Foundation\Resources\Localization\TranslationCatalog;
use Sif\Foundation\Resources\ResourceNamespace;

interface TranslationPlannerInterface
{
    /** @param list<TranslationCatalog> $catalogs */
    public function compile(
        LocaleIdentifier $requested,
        ResourceNamespace $namespace,
        array $catalogs,
        ?LocaleIdentifier $default = null,
    ): ImmutableTranslationPlan;
}
