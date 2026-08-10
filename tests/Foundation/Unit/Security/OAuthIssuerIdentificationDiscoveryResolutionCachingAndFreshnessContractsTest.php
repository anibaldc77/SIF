<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerIssuerValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataCacheInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataDocumentLoaderInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationServerMetadataUriResolverInterface;
use Sif\Foundation\Security\Contracts\OAuthMetadataFreshnessEvaluatorInterface;
use Sif\Foundation\Security\Contracts\OAuthMetadataResolverInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadataCacheEntry;
use Sif\Foundation\Security\OAuth\Metadata\OAuthIssuerIdentifier;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataResolutionContext;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataResolutionResult;

final class OAuthIssuerIdentificationDiscoveryResolutionCachingAndFreshnessContractsTest extends TestCase
{
    public function testIssuerIdentifierUsesExactValueMatching(): void
    {
        $issuer = new OAuthIssuerIdentifier(
            'https://issuer.example.test'
        );

        self::assertTrue(
            $issuer->matches(
                new OAuthIssuerIdentifier(
                    'https://issuer.example.test'
                )
            )
        );

        self::assertFalse(
            $issuer->matches(
                new OAuthIssuerIdentifier(
                    'https://issuer.example.test/'
                )
            )
        );
    }

    public function testResolutionContextMakesCacheAndFreshnessPolicyExplicit(): void
    {
        $context = new OAuthMetadataResolutionContext(
            new OAuthIssuerIdentifier(
                'https://issuer.example.test'
            ),
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            true,
            true
        );

        self::assertTrue($context->allowCached());
        self::assertTrue($context->requireFresh());
        self::assertSame(
            'https://issuer.example.test',
            $context->issuer()->value()
        );
    }

    public function testCacheEntryHasExplicitExpirationSemantics(): void
    {
        $entry = new OAuthAuthorizationServerMetadataCacheEntry(
            $this->metadata(),
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            new DateTimeImmutable('2026-08-09T21:00:00Z')
        );

        self::assertFalse(
            $entry->expiredAt(
                new DateTimeImmutable('2026-08-09T20:59:59Z')
            )
        );
        self::assertTrue(
            $entry->expiredAt(
                new DateTimeImmutable('2026-08-09T21:00:00Z')
            )
        );
    }

    public function testExistingResolverMethodRemainsBackwardCompatible(): void
    {
        $method = new \ReflectionMethod(
            OAuthMetadataResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            OAuthAuthorizationServerMetadata::class,
            (string) $method->getReturnType()
        );
    }

    public function testContextualResolverReturnsTypedResolutionResult(): void
    {
        $method = new \ReflectionMethod(
            OAuthMetadataResolverInterface::class,
            'resolveWithContext'
        );

        self::assertSame(
            OAuthMetadataResolutionResult::class,
            (string) $method->getReturnType()
        );

        $result = new OAuthMetadataResolutionResult(
            $this->metadata(),
            true,
            true,
            'https://issuer.example.test/.well-known/oauth-authorization-server'
        );

        self::assertTrue($result->fromCache());
        self::assertTrue($result->fresh());
    }

    public function testDiscoveryCachingAndIssuerValidationRemainContractDriven(): void
    {
        foreach ([
            OAuthAuthorizationServerIssuerValidatorInterface::class,
            OAuthAuthorizationServerMetadataUriResolverInterface::class,
            OAuthAuthorizationServerMetadataDocumentLoaderInterface::class,
            OAuthAuthorizationServerMetadataCacheInterface::class,
            OAuthMetadataFreshnessEvaluatorInterface::class,
        ] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isInterface()
            );
        }
    }

    public function testDiscoveryLayerRemainsNetworkingAndCacheImplementationNeutral(): void
    {
        foreach ([
            OAuthMetadataResolverInterface::class,
            OAuthAuthorizationServerIssuerValidatorInterface::class,
            OAuthAuthorizationServerMetadataUriResolverInterface::class,
            OAuthAuthorizationServerMetadataDocumentLoaderInterface::class,
            OAuthAuthorizationServerMetadataCacheInterface::class,
            OAuthMetadataFreshnessEvaluatorInterface::class,
            OAuthIssuerIdentifier::class,
            OAuthMetadataResolutionContext::class,
            OAuthAuthorizationServerMetadataCacheEntry::class,
            OAuthMetadataResolutionResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('PDO', $source);
        }
    }

    private function metadata(): OAuthAuthorizationServerMetadata
    {
        return new OAuthAuthorizationServerMetadata(
            'https://issuer.example.test',
            'https://issuer.example.test/authorize',
            'https://issuer.example.test/token'
        );
    }
}
