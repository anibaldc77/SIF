<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationAdapterInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationCapabilityProviderInterface;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapabilities;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapability;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationOutcome;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationService;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailure;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailureKind;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

final class FederatedProviderRevocationCapabilitiesAndRemoteFailureClassificationTest extends TestCase
{
    public function testCapabilitiesAreExplicitAndProviderSpecific(): void
    {
        $capabilities = new FederatedProviderRevocationCapabilities([
            FederatedProviderRevocationCapability::RevokeRefreshToken,
            FederatedProviderRevocationCapability::EndSession,
        ]);

        self::assertTrue(
            $capabilities->supports(
                FederatedProviderRevocationCapability::RevokeRefreshToken
            )
        );
        self::assertFalse(
            $capabilities->supports(
                FederatedProviderRevocationCapability::GlobalLogout
            )
        );
    }

    public function testUnsupportedCapabilityDoesNotCallAdapter(): void
    {
        $adapter = new ProviderRevocationAdapterRecorder();

        $service = new FederatedProviderRevocationService(
            new StaticProviderCapabilityProvider([
                FederatedProviderRevocationCapability::EndSession,
            ]),
            $adapter
        );

        $outcome = $service->revoke(
            $this->identity(),
            FederatedProviderRevocationCapability::GlobalLogout,
            new FederatedRevocationReason('security.incident')
        );

        self::assertFalse($outcome->succeeded());
        self::assertSame(0, $adapter->calls());
        self::assertSame(
            FederatedRemoteFailureKind::Unsupported,
            $outcome->failureDetail()?->kind()
        );
    }

    public function testTransientFailureIsRetryable(): void
    {
        $failure = new FederatedRemoteFailure(
            FederatedRemoteFailureKind::Transient,
            'provider.timeout'
        );

        self::assertTrue($failure->retryable());
        self::assertTrue(
            FederatedProviderRevocationOutcome::failure($failure)->retryable()
        );
    }

    public function testPermanentFailureIsNotRetryable(): void
    {
        $failure = new FederatedRemoteFailure(
            FederatedRemoteFailureKind::Permanent,
            'provider.invalid_client'
        );

        self::assertFalse($failure->retryable());
    }

    public function testSupportedCapabilityDelegatesToAdapter(): void
    {
        $adapter = new ProviderRevocationAdapterRecorder();

        $service = new FederatedProviderRevocationService(
            new StaticProviderCapabilityProvider([
                FederatedProviderRevocationCapability::EndSession,
            ]),
            $adapter
        );

        $outcome = $service->revoke(
            $this->identity(),
            FederatedProviderRevocationCapability::EndSession,
            new FederatedRevocationReason('user.requested')
        );

        self::assertTrue($outcome->succeeded());
        self::assertSame(1, $adapter->calls());
        self::assertSame(
            FederatedProviderRevocationCapability::EndSession,
            $adapter->lastCapability()
        );
    }

    public function testFoundationRemainsProviderAndTransportNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Operations/Provider';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Auth0', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    private function identity(): OidcFederatedIdentity
    {
        return new OidcFederatedIdentity(
            'https://identity.example',
            'subject-123'
        );
    }
}

final readonly class StaticProviderCapabilityProvider implements FederatedProviderRevocationCapabilityProviderInterface
{
    /** @param list<FederatedProviderRevocationCapability> $capabilities */
    public function __construct(private array $capabilities)
    {
    }

    public function capabilitiesFor(
        OidcFederatedIdentity $federatedIdentity
    ): FederatedProviderRevocationCapabilities {
        return new FederatedProviderRevocationCapabilities(
            $this->capabilities
        );
    }
}

final class ProviderRevocationAdapterRecorder implements FederatedProviderRevocationAdapterInterface
{
    private int $calls = 0;
    private ?FederatedProviderRevocationCapability $lastCapability = null;

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationOutcome {
        $this->calls++;
        $this->lastCapability = $capability;

        return FederatedProviderRevocationOutcome::success();
    }

    public function calls(): int
    {
        return $this->calls;
    }

    public function lastCapability(): ?FederatedProviderRevocationCapability
    {
        return $this->lastCapability;
    }
}
