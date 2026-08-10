<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationMetadataMergerInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationMetadataValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationSecurityPolicyProviderInterface;
use Sif\Foundation\Security\Contracts\OAuthSoftwareStatementTrustPolicyInterface;
use Sif\Foundation\Security\Contracts\OAuthSoftwareStatementVerifierInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientMetadataValidationResult;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationSecurityPolicy;
use Sif\Foundation\Security\OAuth\Metadata\OAuthSoftwareStatement;

final class OAuthClientMetadataValidationSoftwareStatementsAndSecurityPolicyContractsTest extends TestCase
{
    public function testSoftwareStatementKeepsTrustRelevantClaimsExplicit(): void
    {
        $statement = new OAuthSoftwareStatement(
            'header.payload.signature',
            'https://software-authority.example.test',
            'software-001',
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            new DateTimeImmutable('2026-08-09T21:00:00Z'),
            ['software_id' => 'software-001']
        );

        self::assertSame(
            'https://software-authority.example.test',
            $statement->issuer()
        );
        self::assertSame('software-001', $statement->subject());
        self::assertSame(
            'software-001',
            $statement->claims()['software_id']
        );
    }

    public function testSecurityPolicyExpressesRegistrationConstraints(): void
    {
        $policy = new OAuthClientRegistrationSecurityPolicy(
            ['authorization_code', 'client_credentials'],
            ['code'],
            ['https'],
            true,
            false
        );

        self::assertSame(
            ['authorization_code', 'client_credentials'],
            $policy->allowedGrantTypes()
        );
        self::assertTrue($policy->requireSoftwareStatement());
        self::assertFalse($policy->allowLoopbackRedirectUris());
    }

    public function testValidationResultReturnsEffectiveMetadata(): void
    {
        $metadata = new OAuthClientRegistrationMetadata(
            'Example Client',
            ['https://client.example.test/callback'],
            ['authorization_code'],
            ['code']
        );

        $result = new OAuthClientMetadataValidationResult(
            $metadata,
            ['scope narrowed by policy']
        );

        self::assertSame(
            'Example Client',
            $result->effectiveMetadata()->clientName()
        );
        self::assertSame(
            ['scope narrowed by policy'],
            $result->warnings()
        );
    }

    public function testExistingValidatorMethodRemainsBackwardCompatible(): void
    {
        $reflection = new \ReflectionClass(
            OAuthClientRegistrationMetadataValidatorInterface::class
        );

        self::assertTrue($reflection->hasMethod('validate'));
        self::assertSame(
            'void',
            (string) $reflection->getMethod('validate')->getReturnType()
        );
    }

    public function testContextualValidationAndMergerAreTyped(): void
    {
        $validate = new \ReflectionMethod(
            OAuthClientRegistrationMetadataValidatorInterface::class,
            'validateWithContext'
        );
        $merge = new \ReflectionMethod(
            OAuthClientRegistrationMetadataMergerInterface::class,
            'merge'
        );

        self::assertSame(
            OAuthClientMetadataValidationResult::class,
            (string) $validate->getReturnType()
        );
        self::assertSame(
            OAuthClientRegistrationMetadata::class,
            (string) $merge->getReturnType()
        );
    }

    public function testSoftwareStatementAndPolicyBoundariesRemainCryptoAndInfrastructureNeutral(): void
    {
        foreach ([
            OAuthSoftwareStatementVerifierInterface::class,
            OAuthSoftwareStatementTrustPolicyInterface::class,
            OAuthClientRegistrationSecurityPolicyProviderInterface::class,
            OAuthClientRegistrationMetadataMergerInterface::class,
            OAuthClientRegistrationMetadataValidatorInterface::class,
            OAuthSoftwareStatement::class,
            OAuthClientRegistrationSecurityPolicy::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('firebase', strtolower($source));
            self::assertStringNotContainsString('lcobucci', strtolower($source));
        }
    }
}
