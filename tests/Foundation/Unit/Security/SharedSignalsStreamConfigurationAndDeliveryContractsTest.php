<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SharedSignalsDeliveryMethodPolicyInterface;
use Sif\Foundation\Security\Contracts\SharedSignalsDeliveryServiceInterface;
use Sif\Foundation\Security\Contracts\SharedSignalsStreamConfigurationRepositoryInterface;
use Sif\Foundation\Security\Contracts\SharedSignalsStreamStatusRepositoryInterface;
use Sif\Foundation\Security\SharedSignals\SecurityEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;
use Sif\Foundation\Security\SharedSignals\SecurityEventToken;
use Sif\Foundation\Security\SharedSignals\SharedSignalsDeliveryEnvelope;
use Sif\Foundation\Security\SharedSignals\SharedSignalsDeliveryResult;
use Sif\Foundation\Security\SharedSignals\SharedSignalsStreamConfiguration;
use Sif\Foundation\Security\SharedSignals\SharedSignalsStreamStatus;

final class SharedSignalsStreamConfigurationAndDeliveryContractsTest extends TestCase
{
    public function testStreamConfigurationKeepsDeliveryAndEventsExplicit(): void
    {
        $configuration = new SharedSignalsStreamConfiguration(
            'stream-001',
            'push',
            ['session-revoked', 'credential-change']
        );

        self::assertSame('stream-001', $configuration->streamId());
        self::assertSame('push', $configuration->deliveryMethod());
        self::assertCount(2, $configuration->eventTypes());
        self::assertTrue($configuration->enabled());
    }

    public function testDeliveryEnvelopeKeepsStreamDeliveryAndTokenExplicit(): void
    {
        $envelope = new SharedSignalsDeliveryEnvelope(
            'stream-001',
            'delivery-001',
            $this->token(),
            new DateTimeImmutable('2026-08-10T12:00:01Z')
        );

        self::assertSame('stream-001', $envelope->streamId());
        self::assertSame('delivery-001', $envelope->deliveryId());
        self::assertSame('set-001', $envelope->token()->tokenId());
    }

    public function testDeliveryResultSeparatesAcceptanceFromRetryability(): void
    {
        $result = new SharedSignalsDeliveryResult(
            false,
            true,
            'temporary_receiver_failure'
        );

        self::assertFalse($result->accepted());
        self::assertTrue($result->retryable());
        self::assertSame(
            'temporary_receiver_failure',
            $result->reason()
        );
    }

    public function testStreamStatusKeepsOperationalStateExplicit(): void
    {
        $status = new SharedSignalsStreamStatus(
            'stream-001',
            true,
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            null
        );

        self::assertSame('stream-001', $status->streamId());
        self::assertTrue($status->enabled());
        self::assertNotNull($status->lastSuccessfulDeliveryAt());
        self::assertNull($status->lastFailedDeliveryAt());
    }

    public function testDeliveryAndRepositoryContractsAreTyped(): void
    {
        $delivery = new \ReflectionMethod(
            SharedSignalsDeliveryServiceInterface::class,
            'deliver'
        );

        self::assertSame(
            SharedSignalsDeliveryResult::class,
            (string) $delivery->getReturnType()
        );

        foreach ([
            SharedSignalsStreamConfigurationRepositoryInterface::class,
            SharedSignalsDeliveryMethodPolicyInterface::class,
            SharedSignalsStreamStatusRepositoryInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testDeliveryLayerRemainsTransportAndStorageNeutral(): void
    {
        foreach ([
            SharedSignalsDeliveryServiceInterface::class,
            SharedSignalsStreamConfigurationRepositoryInterface::class,
            SharedSignalsDeliveryMethodPolicyInterface::class,
            SharedSignalsStreamStatusRepositoryInterface::class,
            SharedSignalsStreamConfiguration::class,
            SharedSignalsDeliveryEnvelope::class,
            SharedSignalsDeliveryResult::class,
            SharedSignalsStreamStatus::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }

    private function token(): SecurityEventToken
    {
        return new SecurityEventToken(
            'https://issuer.example.test',
            'set-001',
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            [
                new SecurityEvent(
                    'session-revoked',
                    new SecurityEventSubject(
                        'account',
                        [
                            'iss' => 'https://issuer.example.test',
                            'sub' => 'user-001',
                        ]
                    ),
                    new DateTimeImmutable('2026-08-10T11:59:59Z')
                ),
            ]
        );
    }
}
