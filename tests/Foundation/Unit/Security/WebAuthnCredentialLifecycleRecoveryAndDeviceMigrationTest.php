<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnCredentialLifecyclePolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnCredentialLifecycleRepositoryInterface;
use Sif\Foundation\Security\Contracts\WebAuthnDeviceMigrationPolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRecoveryPolicyInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialLifecycleEvent;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialLifecycleStatus;
use Sif\Foundation\Security\WebAuthn\WebAuthnDeviceMigrationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnRecoveryContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnRecoveryDecision;

final class WebAuthnCredentialLifecycleRecoveryAndDeviceMigrationTest extends TestCase
{
    public function testLifecycleStatusSupportsOperationalStates(): void
    {
        self::assertSame(
            'active',
            (new WebAuthnCredentialLifecycleStatus(
                WebAuthnCredentialLifecycleStatus::ACTIVE
            ))->value()
        );
        self::assertSame(
            'revoked',
            (new WebAuthnCredentialLifecycleStatus(
                WebAuthnCredentialLifecycleStatus::REVOKED
            ))->value()
        );
    }

    public function testLifecycleEventKeepsCredentialStatusAndReasonExplicit(): void
    {
        $event = new WebAuthnCredentialLifecycleEvent(
            'credential-001',
            new WebAuthnCredentialLifecycleStatus(
                WebAuthnCredentialLifecycleStatus::RETIRED
            ),
            new DateTimeImmutable('2026-08-10T23:30:00Z'),
            'device_replaced'
        );

        self::assertSame('credential-001', $event->credentialId());
        self::assertSame('retired', $event->status()->value());
        self::assertSame('device_replaced', $event->reason());
    }

    public function testRecoveryContextAndDecisionAreExplicit(): void
    {
        $context = new WebAuthnRecoveryContext(
            'user-001',
            ['credential-001', 'credential-002'],
            ['verified_email', 'recovery_code'],
            true
        );

        self::assertSame('user-001', $context->userId());
        self::assertCount(2, $context->availableCredentialIds());
        self::assertTrue($context->highRisk());

        $decision = new WebAuthnRecoveryDecision(
            true,
            ['credential-001'],
            ['step_up_required']
        );

        self::assertTrue($decision->allowed());
        self::assertSame(
            ['credential-001'],
            $decision->credentialsToRetire()
        );
        self::assertSame(
            ['step_up_required'],
            $decision->requiredActions()
        );
    }

    public function testDeviceMigrationContextKeepsSourceAndTargetCapabilitiesExplicit(): void
    {
        $context = new WebAuthnDeviceMigrationContext(
            'user-001',
            ['credential-001'],
            ['internal', 'hybrid'],
            true
        );

        self::assertSame('user-001', $context->userId());
        self::assertSame(
            ['credential-001'],
            $context->sourceCredentialIds()
        );
        self::assertSame(
            ['internal', 'hybrid'],
            $context->targetTransports()
        );
        self::assertTrue($context->syncedCredentialAvailable());
    }

    public function testLifecycleRecoveryAndMigrationContractsAreTyped(): void
    {
        $history = new \ReflectionMethod(
            WebAuthnCredentialLifecycleRepositoryInterface::class,
            'history'
        );
        $recovery = new \ReflectionMethod(
            WebAuthnRecoveryPolicyInterface::class,
            'decide'
        );

        self::assertSame(
            'array',
            (string) $history->getReturnType()
        );
        self::assertSame(
            WebAuthnRecoveryDecision::class,
            (string) $recovery->getReturnType()
        );

        foreach ([
            WebAuthnCredentialLifecyclePolicyInterface::class,
            WebAuthnDeviceMigrationPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testLifecycleLayerRemainsVendorCloudAndStorageNeutral(): void
    {
        foreach ([
            WebAuthnCredentialLifecyclePolicyInterface::class,
            WebAuthnCredentialLifecycleRepositoryInterface::class,
            WebAuthnRecoveryPolicyInterface::class,
            WebAuthnDeviceMigrationPolicyInterface::class,
            WebAuthnCredentialLifecycleStatus::class,
            WebAuthnCredentialLifecycleEvent::class,
            WebAuthnRecoveryContext::class,
            WebAuthnRecoveryDecision::class,
            WebAuthnDeviceMigrationContext::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('iCloud', $source);
            self::assertStringNotContainsString('Google Password Manager', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }
}
