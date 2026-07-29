<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Exceptions\TranslationNotFoundException;
use Sif\Foundation\Resources\ResourceNamespace;

final readonly class ImmutableTranslationPlan
{
    /** @var array<string, TranslationResolution> */
    private array $resolutions;

    /** @param array<string, TranslationResolution> $resolutions */
    public function __construct(
        private LocaleFallbackChain $fallbackChain,
        private ResourceNamespace $namespace,
        array $resolutions,
    ) {
        $this->resolutions = $resolutions;
    }

    public function fallbackChain(): LocaleFallbackChain { return $this->fallbackChain; }
    public function namespace(): ResourceNamespace { return $this->namespace; }
    public function has(TranslationKey $key): bool { return isset($this->resolutions[$key->value()]); }

    public function get(TranslationKey $key): string
    {
        $resolution = $this->resolutions[$key->value()] ?? null;
        if ($resolution === null) {
            throw TranslationNotFoundException::forKey(
                $key->value(),
                $this->fallbackChain->primary()->value(),
                $this->namespace->value(),
            );
        }
        return $resolution->message();
    }

    public function resolution(TranslationKey $key): ?TranslationResolution
    {
        return $this->resolutions[$key->value()] ?? null;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_map(static fn (TranslationResolution $resolution): string => $resolution->message(), $this->resolutions);
    }

    public function count(): int { return count($this->resolutions); }
}
