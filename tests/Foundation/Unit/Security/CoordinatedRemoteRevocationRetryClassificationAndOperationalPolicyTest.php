<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationAdapterInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationCapabilityProviderInterface;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationAssessment;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapabilities;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapability;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCoordinator;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationOutcome;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationPolicy;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationService;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailure;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailureKind;
use Sif\Foundation\Security\Operations\Revocation\FederatedRemoteRetryBridge;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryAdvisor;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryPolicy;

final class CoordinatedRemoteRevocationRetryClassificationAndOperationalPolicyTest extends TestCase
{
    public function testTransientRemoteFailureIsEligibleForRetryPolicy(): void
    {
        $assessment = $this->remoteAssessment(
            FederatedProviderRevocationOutcome::failure(
                new FederatedRemoteFailure(
                    FederatedRemoteFailureKind::Transient,
                    'provider.timeout'
                )
            )
        );

        self::assertTrue($assessment->retryable());
        self::assertFalse($assessment->terminal());

        $retry = $this->retryBridge()->assess(
            $assessment,
            1,
            $this->now()
        );

        self::assertTrue($retry->allowed());
        self::assertNotNull(
            $retry->state()->nextEligibleAt()
        );
    }

    public function testPermanentRemoteFailureIsTerminal(): void
    {
        $assessment = $this->remoteAssessment(
            FederatedProviderRevocationOutcome::failure(
                new FederatedRemoteFailure(
                    FederatedRemoteFailureKind::Permanent,
                    'provider.invalid_client'
                )
            )
        );

        self::assertFalse($assessment->retryable());
        self::assertTrue($assessment->terminal());

        $retry = $this->retryBridge()->assess(
            $assessment,
            1,
            $this->now()
        );

        self::assertFalse($retry->allowed());
        self::assertSame(
            'remote_failure_terminal',
            $retry->reason()
        );
    }

    public function testUnsupportedCapabilityIsNotSilentlySuccessfulByDefault(): void
    {
        $policy = new FederatedProviderRevocationPolicy();

        $outcome = FederatedProviderRevocationOutcome::failure(
            new FederatedRemoteFailure(
                FederatedRemoteFailureKind::Unsupported,
                'provider.capability_unsupported'
            )
        );

        self::assertFalse($policy->retryable($outcome));
        self::assertFalse($policy->terminal($outcome));
    }

    public function testUnsupportedCapabilityCanBeExplicitlyAcceptedAsTerminal(): void
    {
        $policy = new FederatedProviderRevocationPolicy(
            allowUnsupportedAsTerminal: true
        );

        $outcome = FederatedProviderRevocationOutcome::failure(
            new FederatedRemoteFailure(
                FederatedRemoteFailureKind::Unsupported,
                'provider.capability_unsupported'
            )
        );

        self::assertTrue($policy->terminal($outcome));
    }

    public function testSuccessfulRemoteRevocationIsTerminalAndNotRetryable(): void
    {
        $assessment = $this->remoteAssessment(
            FederatedProviderRevocationOutcome::success()
        );

        self::assertFalse($assessment->retryable());
        self::assertTrue($assessment->terminal());
    }

    public function testCoordinatorAndRetryBridgeDoNotScheduleOrPerformTransport(): void
    {
        foreach ([
            FederatedProviderRevocationCoordinator::class,
            FederatedRemoteRetryBridge::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'sleep(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'usleep(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'curl_',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'Keycloak',
                $source
            );
        }
    }

    private function remoteAssessment(
        FederatedProviderRevocationOutcome $outcome
    ): FederatedProviderRevocationAssessment {
        $coordinator = new FederatedProviderRevocationCoordinator(
            new FederatedProviderRevocationService(
                new AllCapabilitiesProvider(),
                new StaticOutcomeRevocationAdapter($outcome)
            ),
            new FederatedProviderRevocationPolicy()
        );

        return $coordinator->execute(
            new OidcFederatedIdentity(
                'https://identity.example',
                'subject-123'
            ),
            FederatedProviderRevocationCapability::EndSession,
            new FederatedRevocationReason(
                'security.incident'
            )
        );
    }

    private function retryBridge(): FederatedRemoteRetryBridge
    {
        return new FederatedRemoteRetryBridge(
            new FederatedRevocationRetryAdvisor(
                new FederatedRevocationRetryPolicy(
                    3,
                    new DateInterval('PT30S')
                )
            )
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T20:00:00+00:00'
        );
    }
}

final readonly class AllCapabilitiesProvider implements FederatedProviderRevocationCapabilityProviderInterface
{
    public function capabilitiesFor(
        OidcFederatedIdentity $federatedIdentity
    ): FederatedProviderRevocationCapabilities {
        return new FederatedProviderRevocationCapabilities([
            FederatedProviderRevocationCapability::RevokeAccessToken,
            FederatedProviderRevocationCapability::RevokeRefreshToken,
            FederatedProviderRevocationCapability::EndSession,
            FederatedProviderRevocationCapability::GlobalLogout,
        ]);
    }
}

final readonly class StaticOutcomeRevocationAdapter implements FederatedProviderRevocationAdapterInterface
{
    public function __construct(
        private FederatedProviderRevocationOutcome $outcome
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationOutcome {
        return $this->outcome;
    }
}
