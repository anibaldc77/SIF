<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\RiscAccountReactionHandlerInterface;
use Sif\Foundation\Security\Contracts\RiscEventPolicyInterface;
use Sif\Foundation\Security\Contracts\RiscSecurityEventMapperInterface;
use Sif\Foundation\Security\SharedSignals\RiscAccountReaction;
use Sif\Foundation\Security\SharedSignals\RiscAccountSecurityEvent;
use Sif\Foundation\Security\SharedSignals\RiscEvaluationResult;
use Sif\Foundation\Security\SharedSignals\RiscEventType;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;

final class RiscAccountSecurityEventsTest extends TestCase
{
    public function testRiscEventTypesCoverAccountSecurityChanges(): void
    {
        self::assertSame(
            'credential-compromise',
            (new RiscEventType(
                RiscEventType::CREDENTIAL_COMPROMISE
            ))->value()
        );

        self::assertSame(
            'account-disabled',
            (new RiscEventType(
                RiscEventType::ACCOUNT_DISABLED
            ))->value()
        );
    }

    public function testAccountSecurityEventKeepsSubjectAndDetailsExplicit(): void
    {
        $event = new RiscAccountSecurityEvent(
            new RiscEventType(
                RiscEventType::IDENTIFIER_CHANGE
            ),
            $this->subject(),
            new DateTimeImmutable('2026-08-10T13:00:00Z'),
            ['previous' => 'old@example.test']
        );

        self::assertSame(
            'identifier-change',
            $event->type()->value()
        );
        self::assertSame('account', $event->subject()->format());
        self::assertSame(
            'old@example.test',
            $event->details()['previous']
        );
    }

    public function testRiscReactionSupportsSecurityActions(): void
    {
        $reaction = new RiscAccountReaction(
            RiscAccountReaction::REVOKE_SESSIONS
        );

        self::assertSame('revoke-sessions', $reaction->action());
    }

    public function testEvaluationResultSeparatesReactionAndReasons(): void
    {
        $result = new RiscEvaluationResult(
            new RiscAccountReaction(
                RiscAccountReaction::DISABLE_ACCESS
            ),
            ['credential compromise confirmed']
        );

        self::assertSame(
            'disable-access',
            $result->reaction()->action()
        );
        self::assertSame(
            ['credential compromise confirmed'],
            $result->reasons()
        );
    }

    public function testRiscContractsAreTyped(): void
    {
        $policy = new \ReflectionMethod(
            RiscEventPolicyInterface::class,
            'evaluate'
        );

        self::assertSame(
            RiscEvaluationResult::class,
            (string) $policy->getReturnType()
        );

        foreach ([
            RiscAccountReactionHandlerInterface::class,
            RiscSecurityEventMapperInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testRiscLayerRemainsAccountImplementationNeutral(): void
    {
        foreach ([
            RiscEventPolicyInterface::class,
            RiscAccountReactionHandlerInterface::class,
            RiscSecurityEventMapperInterface::class,
            RiscAccountSecurityEvent::class,
            RiscAccountReaction::class,
            RiscEvaluationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('session_destroy', strtolower($source));
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
