<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Exceptions\InvalidTranslationCatalogException;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePriority;

final readonly class TranslationCatalog
{
    private string $identifier;

    /** @var array<string, string> */
    private array $messages;

    /** @param array<string, string> $messages */
    public function __construct(
        string $identifier,
        private LocaleIdentifier $locale,
        private ResourceNamespace $namespace,
        array $messages,
        private ResourcePriority $priority = new ResourcePriority(),
    ) {
        $identifier = trim($identifier);
        if ($identifier === '' || strlen($identifier) > 128 || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $identifier) !== 1) {
            throw new InvalidTranslationCatalogException(sprintf('Invalid translation catalog identifier "%s".', $identifier));
        }
        if ($messages === []) {
            throw new InvalidTranslationCatalogException('Translation catalogs must contain at least one message.');
        }

        $validated = [];
        foreach ($messages as $key => $message) {
            $translationKey = new TranslationKey($key);
            if (str_contains($message, "\0")) {
                throw new InvalidTranslationCatalogException('Translation messages must not contain null bytes.');
            }
            $validated[$translationKey->value()] = $message;
        }

        $this->identifier = $identifier;
        $this->messages = $validated;
    }

    public function identifier(): string { return $this->identifier; }
    public function locale(): LocaleIdentifier { return $this->locale; }
    public function namespace(): ResourceNamespace { return $this->namespace; }
    public function priority(): ResourcePriority { return $this->priority; }

    /** @return array<string, string> */
    public function messages(): array { return $this->messages; }
    public function has(TranslationKey $key): bool { return array_key_exists($key->value(), $this->messages); }
    public function get(TranslationKey $key): ?string { return $this->messages[$key->value()] ?? null; }
    public function qualifiedIdentifier(): string
    {
        return $this->namespace->value() . ':' . $this->locale->value() . ':' . $this->identifier;
    }
}
