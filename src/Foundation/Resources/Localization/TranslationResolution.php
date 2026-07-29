<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

final readonly class TranslationResolution
{
    public function __construct(
        private TranslationKey $key,
        private string $message,
        private LocaleIdentifier $resolvedLocale,
        private string $catalogIdentifier,
        private int $catalogOrder,
    ) {
    }

    public function key(): TranslationKey { return $this->key; }
    public function message(): string { return $this->message; }
    public function resolvedLocale(): LocaleIdentifier { return $this->resolvedLocale; }
    public function catalogIdentifier(): string { return $this->catalogIdentifier; }
    public function catalogOrder(): int { return $this->catalogOrder; }
}
