<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Contracts\LocaleFallbackChainBuilderInterface;
use Sif\Foundation\Resources\Contracts\TranslationPlannerInterface;
use Sif\Foundation\Resources\Exceptions\DuplicateTranslationCatalogException;
use Sif\Foundation\Resources\ResourceNamespace;

final readonly class DeterministicTranslationPlanner implements TranslationPlannerInterface
{
    public function __construct(
        private LocaleFallbackChainBuilderInterface $fallbackBuilder = new DeterministicLocaleFallbackChainBuilder(),
    ) {
    }

    public function compile(
        LocaleIdentifier $requested,
        ResourceNamespace $namespace,
        array $catalogs,
        ?LocaleIdentifier $default = null,
    ): ImmutableTranslationPlan {
        $chain = $this->fallbackBuilder->build($requested, $default);
        /** @var array<string, array{catalog:TranslationCatalog,order:int}> $unique */
        $unique = [];
        foreach ($catalogs as $order => $catalog) {
            $qualified = $catalog->qualifiedIdentifier();
            if (isset($unique[$qualified])) {
                throw new DuplicateTranslationCatalogException(sprintf(
                    'Translation catalog "%s" is already registered.',
                    $qualified,
                ));
            }
            $unique[$qualified] = ['catalog' => $catalog, 'order' => $order];
        }

        /** @var array<string, TranslationResolution> $resolutions */
        $resolutions = [];
        foreach ($chain->locales() as $locale) {
            $matching = array_values(array_filter(
                $unique,
                static fn (array $entry): bool => $entry['catalog']->locale()->equals($locale)
                    && $entry['catalog']->namespace()->equals($namespace),
            ));
            usort($matching, static function (array $left, array $right): int {
                $priority = $right['catalog']->priority()->compare($left['catalog']->priority());
                return $priority !== 0 ? $priority : $left['order'] <=> $right['order'];
            });

            foreach ($matching as $entry) {
                foreach ($entry['catalog']->messages() as $key => $message) {
                    if (isset($resolutions[$key])) {
                        continue;
                    }
                    $translationKey = new TranslationKey($key);
                    $resolutions[$key] = new TranslationResolution(
                        $translationKey,
                        $message,
                        $locale,
                        $entry['catalog']->identifier(),
                        $entry['order'],
                    );
                }
            }
        }

        ksort($resolutions);
        return new ImmutableTranslationPlan($chain, $namespace, $resolutions);
    }
}
