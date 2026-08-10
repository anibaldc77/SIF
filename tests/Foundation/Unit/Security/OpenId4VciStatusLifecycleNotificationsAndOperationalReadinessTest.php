<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialIssuanceLifecyclePolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceNotificationHandlerInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceNotificationPublisherInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuanceOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceLifecycleAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceLifecycleStatus;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceNotification;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceOperationalContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceOperationalReadinessReport;

final class OpenId4VciStatusLifecycleNotificationsAndOperationalReadinessTest extends TestCase
{
    public function testLifecycleStatusSupportsOperationalStates(): void
    {
        self::assertSame(
            'pending',
            (new CredentialIssuanceLifecycleStatus(
                CredentialIssuanceLifecycleStatus::PENDING
            ))->value()
        );
        self::assertSame(
            'accepted',
            (new CredentialIssuanceLifecycleStatus(
                CredentialIssuanceLifecycleStatus::ACCEPTED
            ))->value()
        );
    }

    public function testNotificationKeepsTransactionEventAndMetadataExplicit(): void
    {
        $notification = new CredentialIssuanceNotification(
            'notification-001',
            'transaction-001',
            'credential_accepted',
            ['credential_configuration_id' => 'identity-credential']
        );

        self::assertSame(
            'notification-001',
            $notification->notificationId()
        );
        self::assertSame(
            'transaction-001',
            $notification->transactionId()
        );
        self::assertSame(
            'credential_accepted',
            $notification->event()
        );
        self::assertSame(
            'identity-credential',
            $notification->metadata()['credential_configuration_id']
        );
    }

    public function testLifecycleAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new CredentialIssuanceLifecycleAssessment(
            false,
            ['invalid_transition'],
            ['notification_pending']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(
            ['invalid_transition'],
            $assessment->violations()
        );
        self::assertSame(
            ['notification_pending'],
            $assessment->warnings()
        );
    }

    public function testOperationalContextKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new CredentialIssuanceOperationalContext(
            [
                'authorization-code',
                'pre-authorized-code',
                'proof-validation',
                'batch-issuance',
                'deferred-issuance',
                'metadata-discovery',
                'transaction-binding',
                'notifications',
            ],
            [
                'replay-protection',
                'nonce-rotation',
                'transaction-validation',
                'audit',
            ]
        );

        self::assertContains(
            'deferred-issuance',
            $context->availableCapabilities()
        );
        self::assertContains(
            'replay-protection',
            $context->activeControls()
        );
    }

    public function testOperationalReadinessSeparatesBlockingIssuesAndAdvisories(): void
    {
        $report = new CredentialIssuanceOperationalReadinessReport(
            false,
            ['proof_verifier_unavailable'],
            ['notification delivery policy requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['proof_verifier_unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['notification delivery policy requires review'],
            $report->advisories()
        );
    }

    public function testLifecycleNotificationAndReadinessContractsAreTyped(): void
    {
        $lifecycle = new \ReflectionMethod(
            CredentialIssuanceLifecyclePolicyInterface::class,
            'assess'
        );
        $readiness = new \ReflectionMethod(
            CredentialIssuanceOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            CredentialIssuanceLifecycleAssessment::class,
            (string) $lifecycle->getReturnType()
        );
        self::assertSame(
            CredentialIssuanceOperationalReadinessReport::class,
            (string) $readiness->getReturnType()
        );

        foreach ([
            CredentialIssuanceNotificationPublisherInterface::class,
            CredentialIssuanceNotificationHandlerInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testOperationalLayerRemainsTransportQueueAndStorageNeutral(): void
    {
        foreach ([
            CredentialIssuanceLifecyclePolicyInterface::class,
            CredentialIssuanceNotificationPublisherInterface::class,
            CredentialIssuanceNotificationHandlerInterface::class,
            CredentialIssuanceOperationalReadinessEvaluatorInterface::class,
            CredentialIssuanceLifecycleStatus::class,
            CredentialIssuanceNotification::class,
            CredentialIssuanceOperationalContext::class,
            CredentialIssuanceOperationalReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('RabbitMQ', $source);
            self::assertStringNotContainsString('Kafka', $source);
        }
    }
}
