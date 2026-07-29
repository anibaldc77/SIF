<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Resources\Exceptions\DuplicateTranslationCatalogException;
use Sif\Foundation\Resources\Exceptions\InvalidLocaleFallbackChainException;
use Sif\Foundation\Resources\Exceptions\InvalidLocaleIdentifierException;
use Sif\Foundation\Resources\Exceptions\InvalidTranslationCatalogException;
use Sif\Foundation\Resources\Exceptions\TranslationNotFoundException;
use Sif\Foundation\Resources\Localization\DeterministicLocaleFallbackChainBuilder;
use Sif\Foundation\Resources\Localization\DeterministicTranslationPlanner;
use Sif\Foundation\Resources\Localization\LocaleFallbackChain;
use Sif\Foundation\Resources\Localization\LocaleIdentifier;
use Sif\Foundation\Resources\Localization\TranslationCatalog;
use Sif\Foundation\Resources\Localization\TranslationKey;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePriority;

final class TranslationPlanningTest extends TestCase
{
    public function test_locale_identifier_is_canonicalized(): void
    {
        self::assertSame('es-AR', (new LocaleIdentifier('ES_ar'))->value());
        self::assertSame('zh-Hant-TW', (new LocaleIdentifier('zh-hant-tw'))->value());
    }

    public function test_invalid_locale_identifier_is_rejected(): void
    {
        $this->expectException(InvalidLocaleIdentifierException::class);
        new LocaleIdentifier('../es');
    }

    public function test_fallback_chain_contains_locale_hierarchy_and_default(): void
    {
        $chain = (new DeterministicLocaleFallbackChainBuilder())->build(
            new LocaleIdentifier('es-AR'),
            new LocaleIdentifier('en-GB'),
        );

        self::assertSame(['es-AR', 'es', 'en-GB', 'en'], $chain->values());
    }

    public function test_duplicate_fallback_locale_is_rejected(): void
    {
        $this->expectException(InvalidLocaleFallbackChainException::class);
        new LocaleFallbackChain([new LocaleIdentifier('es'), new LocaleIdentifier('es')]);
    }

    public function test_exact_locale_precedes_parent_and_default(): void
    {
        $plan = (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es-AR'),
            new ResourceNamespace('application'),
            [
                $this->catalog('default', 'en', ['greeting' => 'Hello']),
                $this->catalog('parent', 'es', ['greeting' => 'Hola', 'cancel' => 'Cancelar']),
                $this->catalog('exact', 'es-AR', ['greeting' => 'Buen día']),
            ],
            new LocaleIdentifier('en'),
        );

        self::assertSame('Buen día', $plan->get(new TranslationKey('greeting')));
        self::assertSame('Cancelar', $plan->get(new TranslationKey('cancel')));
        self::assertSame('es-AR', $plan->resolution(new TranslationKey('greeting'))?->resolvedLocale()->value());
    }

    public function test_priority_orders_catalogs_within_same_locale(): void
    {
        $plan = (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es'),
            new ResourceNamespace('application'),
            [
                $this->catalog('low', 'es', ['save' => 'Guardar base'], 0),
                $this->catalog('high', 'es', ['save' => 'Guardar'], 100),
            ],
        );

        self::assertSame('Guardar', $plan->get(new TranslationKey('save')));
        self::assertSame('high', $plan->resolution(new TranslationKey('save'))?->catalogIdentifier());
    }

    public function test_equal_priority_preserves_catalog_input_order(): void
    {
        $plan = (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es'),
            new ResourceNamespace('application'),
            [
                $this->catalog('first', 'es', ['save' => 'Primero'], 10),
                $this->catalog('second', 'es', ['save' => 'Segundo'], 10),
            ],
        );

        self::assertSame('Primero', $plan->get(new TranslationKey('save')));
    }

    public function test_namespace_isolation_is_preserved(): void
    {
        $plan = (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es'),
            new ResourceNamespace('admin'),
            [
                $this->catalog('app', 'es', ['title' => 'Aplicación'], namespace: 'application'),
                $this->catalog('admin', 'es', ['title' => 'Administración'], namespace: 'admin'),
            ],
        );

        self::assertSame('Administración', $plan->get(new TranslationKey('title')));
    }

    public function test_duplicate_catalog_identity_is_rejected(): void
    {
        $this->expectException(DuplicateTranslationCatalogException::class);

        (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es'),
            new ResourceNamespace('application'),
            [
                $this->catalog('messages', 'es', ['a' => 'A']),
                $this->catalog('messages', 'es', ['b' => 'B']),
            ],
        );
    }

    public function test_missing_translation_throws_typed_exception(): void
    {
        $plan = (new DeterministicTranslationPlanner())->compile(
            new LocaleIdentifier('es'),
            new ResourceNamespace('application'),
            [$this->catalog('messages', 'es', ['known' => 'Conocido'])],
        );

        $this->expectException(TranslationNotFoundException::class);
        $plan->get(new TranslationKey('missing'));
    }

    public function test_empty_catalog_is_rejected(): void
    {
        $this->expectException(InvalidTranslationCatalogException::class);
        $this->catalog('empty', 'es', []);
    }

    /** @param array<string, string> $messages */
    private function catalog(
        string $identifier,
        string $locale,
        array $messages,
        int $priority = 0,
        string $namespace = 'application',
    ): TranslationCatalog {
        return new TranslationCatalog(
            $identifier,
            new LocaleIdentifier($locale),
            new ResourceNamespace($namespace),
            $messages,
            new ResourcePriority($priority),
        );
    }
}
