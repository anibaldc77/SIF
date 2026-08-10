<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationManagementAuthorizerInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationManagementServiceInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRegistrationRecordRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthRegistrationAccessTokenRotatorInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationManagementAuthorization;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationRecord;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationUpdate;

final class OAuthClientRegistrationManagementLifecycleContractsTest extends TestCase
{
    public function testRegistrationRecordKeepsLifecycleStateExplicit(): void
    {
        $record = $this->record();

        self::assertSame('client-001', $record->client()->id()->value());
        self::assertSame('Example Client', $record->metadata()->clientName());
        self::assertTrue($record->active());
        self::assertSame(
            'https://issuer.example.test/register/client-001',
            $record->registrationClientUri()
        );
    }

    public function testManagementAuthorizationUsesOpaqueAccessTokenReference(): void
    {
        $authorization = new OAuthClientRegistrationManagementAuthorization(
            'client-001',
            'vault://oauth/registrations/client-001/access-token'
        );

        self::assertSame('client-001', $authorization->clientId());
        self::assertSame(
            'vault://oauth/registrations/client-001/access-token',
            $authorization->registrationAccessTokenReference()
        );
    }

    public function testUpdateSeparatesClientIdFromRequestedMetadata(): void
    {
        $update = new OAuthClientRegistrationUpdate(
            'client-001',
            new OAuthClientRegistrationMetadata(
                'Updated Client',
                ['https://client.example.test/callback'],
                ['authorization_code'],
                ['code']
            )
        );

        self::assertSame('client-001', $update->clientId());
        self::assertSame(
            'Updated Client',
            $update->metadata()->clientName()
        );
    }

    public function testManagementServiceExposesReadUpdateDeleteLifecycle(): void
    {
        $reflection = new \ReflectionClass(
            OAuthClientRegistrationManagementServiceInterface::class
        );

        self::assertTrue($reflection->hasMethod('get'));
        self::assertTrue($reflection->hasMethod('update'));
        self::assertTrue($reflection->hasMethod('delete'));

        self::assertSame(
            OAuthClientRegistrationRecord::class,
            (string) $reflection->getMethod('get')->getReturnType()
        );
        self::assertSame(
            OAuthClientRegistrationRecord::class,
            (string) $reflection->getMethod('update')->getReturnType()
        );
    }

    public function testRepositoryAuthorizerAndRotationRemainContractDriven(): void
    {
        self::assertTrue(
            (new \ReflectionClass(
                OAuthClientRegistrationRecordRepositoryInterface::class
            ))->isInterface()
        );
        self::assertTrue(
            (new \ReflectionClass(
                OAuthClientRegistrationManagementAuthorizerInterface::class
            ))->isInterface()
        );

        $rotate = new \ReflectionMethod(
            OAuthRegistrationAccessTokenRotatorInterface::class,
            'rotate'
        );

        self::assertSame('string', (string) $rotate->getReturnType());
    }

    public function testManagementLifecycleRemainsTransportStorageAndSecretNeutral(): void
    {
        foreach ([
            OAuthClientRegistrationManagementServiceInterface::class,
            OAuthClientRegistrationRecordRepositoryInterface::class,
            OAuthClientRegistrationManagementAuthorizerInterface::class,
            OAuthRegistrationAccessTokenRotatorInterface::class,
            OAuthClientRegistrationManagementAuthorization::class,
            OAuthClientRegistrationRecord::class,
            OAuthClientRegistrationUpdate::class,
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
            self::assertStringNotContainsString(
                'plainTextSecret',
                $source
            );
        }
    }

    private function record(): OAuthClientRegistrationRecord
    {
        return new OAuthClientRegistrationRecord(
            new OAuthClient(
                new OAuthClientId('client-001'),
                'Example Client',
                false,
                [new OAuthRedirectUri(
                    'https://client.example.test/callback'
                )],
                []
            ),
            new OAuthClientRegistrationMetadata(
                'Example Client',
                ['https://client.example.test/callback'],
                ['authorization_code'],
                ['code']
            ),
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            new DateTimeImmutable('2026-08-09T20:00:00Z'),
            true,
            'https://issuer.example.test/register/client-001',
            'vault://oauth/registrations/client-001/access-token'
        );
    }
}
