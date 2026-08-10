<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationCredentialIssuerInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationPolicyInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationResultSerializerInterface;
use Sif\Foundation\Security\Contracts\OAuthDynamicClientRegistrationServiceInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationCredential;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthDynamicClientRegistrationResult;

final class OAuthDynamicClientRegistrationModelAndContractsTest extends TestCase
{
    public function testCredentialUsesOpaqueMaterialReferenceRatherThanOwningSecretStorage(): void
    {
        $credential = new OAuthClientRegistrationCredential(
            'client_secret',
            'vault://oauth/clients/client-001/secret',
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            new DateTimeImmutable('2027-08-09T20:00:00Z')
        );

        self::assertSame('client_secret', $credential->type());
        self::assertSame(
            'vault://oauth/clients/client-001/secret',
            $credential->materialReference()
        );
        self::assertSame(
            '2027-08-09T20:00:00+00:00',
            $credential->expiresAt()?->format(DATE_ATOM)
        );
    }

    public function testRegistrationResultKeepsClientMetadataAndManagementReferences(): void
    {
        $metadata = new OAuthClientRegistrationMetadata(
            'Example Client',
            ['https://client.example.test/callback'],
            ['authorization_code'],
            ['code']
        );

        $client = new OAuthClient(
            new OAuthClientId('client-001'),
            'Example Client',
            false,
            [new OAuthRedirectUri('https://client.example.test/callback')],
            []
        );

        $result = new OAuthDynamicClientRegistrationResult(
            $client,
            $metadata,
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            [],
            'https://issuer.example.test/register/client-001',
            'vault://oauth/registrations/client-001/access-token'
        );

        self::assertSame('client-001', $result->client()->id()->value());
        self::assertSame(
            'Example Client',
            $result->registeredMetadata()->clientName()
        );
        self::assertSame(
            'https://issuer.example.test/register/client-001',
            $result->registrationClientUri()
        );
        self::assertSame(
            'vault://oauth/registrations/client-001/access-token',
            $result->registrationAccessTokenReference()
        );
    }

    public function testExistingRegistrationMethodRemainsBackwardCompatible(): void
    {
        $method = new \ReflectionMethod(
            OAuthDynamicClientRegistrationServiceInterface::class,
            'register'
        );

        self::assertSame(
            OAuthClient::class,
            (string) $method->getReturnType()
        );
    }

    public function testExtendedRegistrationMethodReturnsTypedResult(): void
    {
        $method = new \ReflectionMethod(
            OAuthDynamicClientRegistrationServiceInterface::class,
            'registerWithResult'
        );

        self::assertSame(
            OAuthDynamicClientRegistrationResult::class,
            (string) $method->getReturnType()
        );
    }

    public function testRegistrationSupportingContractsAreTyped(): void
    {
        $issuer = new \ReflectionMethod(
            OAuthClientRegistrationCredentialIssuerInterface::class,
            'issueFor'
        );
        $serializer = new \ReflectionMethod(
            OAuthClientRegistrationResultSerializerInterface::class,
            'serialize'
        );
        $policy = new \ReflectionMethod(
            OAuthClientRegistrationPolicyInterface::class,
            'authorize'
        );

        self::assertSame('array', (string) $issuer->getReturnType());
        self::assertSame('array', (string) $serializer->getReturnType());
        self::assertSame('void', (string) $policy->getReturnType());
    }

    public function testDynamicRegistrationRemainsTransportAndStorageNeutral(): void
    {
        foreach ([
            OAuthDynamicClientRegistrationServiceInterface::class,
            OAuthClientRegistrationCredentialIssuerInterface::class,
            OAuthClientRegistrationResultSerializerInterface::class,
            OAuthClientRegistrationPolicyInterface::class,
            OAuthClientRegistrationCredential::class,
            OAuthDynamicClientRegistrationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
        }
    }
}
