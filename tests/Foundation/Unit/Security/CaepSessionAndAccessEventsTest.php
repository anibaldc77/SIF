<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CaepEventPolicyInterface;
use Sif\Foundation\Security\Contracts\CaepSecurityEventMapperInterface;
use Sif\Foundation\Security\Contracts\CaepSessionReactionHandlerInterface;
use Sif\Foundation\Security\SharedSignals\CaepAccessReaction;
use Sif\Foundation\Security\SharedSignals\CaepEvaluationResult;
use Sif\Foundation\Security\SharedSignals\CaepEventType;
use Sif\Foundation\Security\SharedSignals\CaepSessionEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;

final class CaepSessionAndAccessEventsTest extends TestCase
{
    public function testCaepEventTypeSupportsSessionAndAccessChanges(): void
    {
        self::assertSame(
            'session-revoked',
            (new CaepEventType(CaepEventType::SESSION_REVOKED))->value()
        );
        self::assertSame(
            'assurance-level-change',
            (new CaepEventType(
                CaepEventType::ASSURANCE_LEVEL_CHANGE
            ))->value()
        );
    }

    public function testCaepSessionEventKeepsSubjectTimeAndDetailsExplicit(): void
    {
        $event = new CaepSessionEvent(
            new CaepEventType(CaepEventType::TOKEN_CLAIMS_CHANGE),
            $this->subject(),
            new DateTimeImmutable('2026-08-10T12:30:00Z'),
            ['claims' => ['role']]
        );

        self::assertSame(
            'token-claims-change',
            $event->type()->value()
        );
        self::assertSame('account', $event->subject()->format());
        self::assertSame(['role'], $event->details()['claims']);
    }

    public function testAccessReactionSupportsContinuousAccessActions(): void
    {
        $reaction = new CaepAccessReaction(
            CaepAccessReaction::REVOKE_SESSION
        );

        self::assertSame('revoke-session', $reaction->action());
    }

    public function testEvaluationResultSeparatesReactionFromReasons(): void
    {
        $result = new CaepEvaluationResult(
            new CaepAccessReaction(
                CaepAccessReaction::REAUTHENTICATE
            ),
            ['assurance level decreased']
        );

        self::assertSame(
            'reauthenticate',
            $result->reaction()->action()
        );
        self::assertSame(
            ['assurance level decreased'],
            $result->reasons()
        );
    }

    public function testCaepContractsAreTyped(): void
    {
        $policy = new \ReflectionMethod(
            CaepEventPolicyInterface::class,
            'evaluate'
        );

        self::assertSame(
            CaepEvaluationResult::class,
            (string) $policy->getReturnType()
        );

        foreach ([
            CaepSessionReactionHandlerInterface::class,
            CaepSecurityEventMapperInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testCaepLayerRemainsSessionAndTokenImplementationNeutral(): void
    {
        foreach ([
            CaepEventPolicyInterface::class,
            CaepSessionReactionHandlerInterface::class,
            CaepSecurityEventMapperInterface::class,
            CaepSessionEvent::class,
            CaepAccessReaction::class,
            CaepEvaluationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('session_destroy', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    private function subject(): SecurityEventSubject
    {
        return new SecurityEventSubject(
            'account',
            [
                'iss' => 'https://issuer.example.test',
                'sub' => 'user-001',
            ]
        );
    }
}
