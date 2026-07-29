<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Exceptions\InvalidLocaleFallbackChainException;

final readonly class LocaleFallbackChain
{
    /** @var list<LocaleIdentifier> */
    private array $locales;

    /** @param list<LocaleIdentifier> $locales */
    public function __construct(array $locales)
    {
        if ($locales === []) {
            throw new InvalidLocaleFallbackChainException('Locale fallback chains cannot be empty.');
        }

        $seen = [];
        foreach ($locales as $locale) {
            if (isset($seen[$locale->value()])) {
                throw new InvalidLocaleFallbackChainException(sprintf(
                    'Locale fallback chain contains duplicate locale "%s".',
                    $locale->value(),
                ));
            }
            $seen[$locale->value()] = true;
        }
        $this->locales = array_values($locales);
    }

    /** @return list<LocaleIdentifier> */
    public function locales(): array { return $this->locales; }
    public function primary(): LocaleIdentifier { return $this->locales[0]; }

    /** @return list<string> */
    public function values(): array
    {
        return array_map(static fn (LocaleIdentifier $locale): string => $locale->value(), $this->locales);
    }
}
