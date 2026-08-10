<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ContinuousAccessEvaluationHandlerInterface;
use Sif\Foundation\Security\Contracts\SecurityEventPublisherInterface;
use Sif\Foundation\Security\Contracts\SecurityEventTokenVerifierInterface;
use Sif\Foundation\Security\Contracts\SharedSignalsStreamRepositoryInterface;
use Sif\Foundation\Security\SharedSignals\SecurityEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;
use Sif\Foundation\Security\SharedSignals\SecurityEventToken;
use Sif\Foundation\Security\SharedSignals\SharedSignalsStream;

final class SharedSignalsAndContinuousAccessEvaluationArchitectureTest extends TestCase
{
    public function testSecurityEventSubjectKeepsFormatAndAttributesExplicit(): void
    {
        $subject = new SecurityEventSubject(
            'account',
            ['iss' => 'https://issuer.example.test', 'sub' => 'user-001']
        );

        self::assertSame('account', $subject->format());
        self::assertSame('user-001', $subject->attributes()['sub']);
    }

    public function testSecurityEventKeepsTypeSubjectAndOccurrenceExplicit(): void
    {
        $event = new SecurityEvent(
            'session-revoked',
            $this->subject(),
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            ['reason' => 'risk-elevated']
        );

        self::assertSame('session-revoked', $event->type());
        self::assertSame('account', $event->subject()->format());
        self::assertSame('risk-elevated', $event->payload()['reason']);
    }

    public function testSecurityEventTokenContainsOneOrMoreEvents(): void
    {
        $event = new SecurityEvent(
            'credential-change',
            $this->subject(),
            new DateTimeImmutable('2026-08-10T12:00:00Z')
        );

        $token = new SecurityEventToken(
            'https://issuer.example.test',
            'set-001',
            new DateTimeImmutable('2026-08-10T12:00:01Z'),
            [$event]
        );

        self::assertSame('set-001', $token->tokenId());
        self::assertCount(1, $token->events());
    }

    public function testSharedSignalsStreamKeepsIssuerAudienceAndEventsExplicit(): void
    {
        $stream = new SharedSignalsStream(
            'stream-001',
            'https://issuer.example.test',
            'https://receiver.example.test',
            ['session-revoked', 'credential-change']
        );

        self::assertSame('stream-001', $stream->streamId());
        self::assertSame(
            'https://receiver.example.test',
            $stream->audience()
        );
        self::assertCount(2, $stream->eventTypes());
    }

    public function testArchitectureContractsAreTyped(): void
    {
        $verify = new \ReflectionMethod(
            SecurityEventTokenVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            SecurityEventToken::class,
            (string) $verify->getReturnType()
        );

        foreach ([
            SecurityEventPublisherInterface::class,
            SharedSignalsStreamRepositoryInterface::class,
            ContinuousAccessEvaluationHandlerInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testSharedSignalsLayerRemainsTransportAndCryptoNeutral(): void
    {
        foreach ([
            SecurityEventTokenVerifierInterface::class,
            SecurityEventPublisherInterface::class,
            SharedSignalsStreamRepositoryInterface::class,
            ContinuousAccessEvaluationHandlerInterface::class,
            SecurityEvent::class,
            SecurityEventSubject::class,
            SecurityEventToken::class,
            SharedSignalsStream::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
        }
    }

    private function subject(): SecurityEventSubject
    {
        return new SecurityEventSubject(
            'account',
            ['iss' => 'https://issuer.example.test', 'sub' => 'user-001']
        );
    }
}
