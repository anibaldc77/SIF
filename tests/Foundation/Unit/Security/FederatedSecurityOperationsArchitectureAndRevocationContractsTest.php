<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderCredentialRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityOperationEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionRevokerInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;

final class FederatedSecurityOperationsArchitectureAndRevocationContractsTest extends TestCase
{
    public function testRevocationRequestSeparatesLocalAndFederatedIdentity(): void
    {
        $request = new FederatedRevocationRequest(
            new IdentityId('local-user-1'),
            new OidcFederatedIdentity(
                'https://identity.example',
                'subject-123'
            ),
            FederatedRevocationScope::All,
            new FederatedRevocationReason('security.incident')
        );

        self::assertSame(
            'local-user-1',
            $request->localIdentityId()->value()
        );
        self::assertSame(
            'subject-123',
            $request->federatedIdentity()->subject()
        );
        self::assertSame(
            FederatedRevocationScope::All,
            $request->scope()
        );
    }

    public function testRevocationReasonIsBoundedAndStructured(): void
    {
        $reason = new FederatedRevocationReason(
            'user.requested',
            'User requested federated access revocation.'
        );

        self::assertSame(
            'user.requested',
            $reason->code()
        );
        self::assertSame(
            'User requested federated access revocation.',
            $reason->detail()
        );
    }

    public function testRevocationContractsRemainStorageAndProviderNeutral(): void
    {
        foreach ([
            FederatedSessionRevokerInterface::class,
            FederatedIdentityLinkRevokerInterface::class,
            FederatedProviderCredentialRevokerInterface::class,
            FederatedSecurityOperationEventPublisherInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SQL', strtoupper($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Auth0', $source);
            self::assertStringNotContainsString('Okta', $source);
        }
    }

    public function testScopeRequiresExplicitOperationalIntent(): void
    {
        self::assertSame(
            'local_sessions',
            FederatedRevocationScope::LocalSessions->value
        );
        self::assertSame(
            'provider_credentials',
            FederatedRevocationScope::ProviderCredentials->value
        );
        self::assertSame(
            'identity_link',
            FederatedRevocationScope::IdentityLink->value
        );
        self::assertSame(
            'all',
            FederatedRevocationScope::All->value
        );
    }

    public function testSecurityOperationEventContainsNoTokenSpecificField(): void
    {
        $event = new FederatedSecurityOperationEvent(
            'federation.revocation.requested',
            new \DateTimeImmutable('2026-08-07T18:00:00+00:00'),
            [
                'identity_id' => 'local-user-1',
                'reason' => 'security.incident',
            ]
        );

        self::assertSame(
            'federation.revocation.requested',
            $event->name()
        );

        $serialized = json_encode($event->context());
        self::assertIsString($serialized);
        self::assertStringNotContainsString(
            'access_token',
            strtolower($serialized)
        );
        self::assertStringNotContainsString(
            'refresh_token',
            strtolower($serialized)
        );
        self::assertStringNotContainsString(
            'id_token',
            strtolower($serialized)
        );
    }

    public function testOperationsFoundationDoesNotRevokeAnythingByItselfYet(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Operations';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        ) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'session_destroy(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'setcookie(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'curl_',
                strtolower($source)
            );
        }
    }
}
