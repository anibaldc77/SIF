<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Contracts\LocaleFallbackChainBuilderInterface;

final readonly class DeterministicLocaleFallbackChainBuilder implements LocaleFallbackChainBuilderInterface
{
    public function build(LocaleIdentifier $requested, ?LocaleIdentifier $default = null): LocaleFallbackChain
    {
        /** @var array<string, LocaleIdentifier> $ordered */
        $ordered = [];
        foreach ($requested->hierarchy() as $locale) {
            $ordered[$locale->value()] = $locale;
        }
        if ($default !== null) {
            foreach ($default->hierarchy() as $locale) {
                $ordered[$locale->value()] ??= $locale;
            }
        }

        return new LocaleFallbackChain(array_values($ordered));
    }
}
