<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ContinuousAccessDecisionPolicyInterface;
use Sif\Foundation\Security\Contracts\ContinuousAccessReactionExecutorInterface;
use Sif\Foundation\Security\Contracts\ReauthenticationRequirementServiceInterface;
use Sif\Foundation\Security\Contracts\SessionRevocationServiceInterface;
use Sif\Foundation\Security\Contracts\TokenRevocationServiceInterface;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessAction;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessContext;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessDecision;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessExecutionResult;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;

final class ContinuousSessionAndTokenReactionPoliciesTest extends TestCase
{
    public function testContinuousAccessActionSupportsSessionAndTokenResponses(): void
    {
        self::assertSame(
            'revoke-session',
            (new ContinuousAccessAction(
                ContinuousAccessAction::REVOKE_SESSION
            ))->value()
        );
        self::assertSame(
            'revoke-tokens',
            (new ContinuousAccessAction(
                ContinuousAccessAction::REVOKE_TOKENS
            ))->value()
        );
    }

    public function testDecisionSeparatesActionsAndReasons(): void
    {
        $decision = new ContinuousAccessDecision(
            [
                new ContinuousAccessAction(
                    ContinuousAccessAction::REQUIRE_REAUTHENTICATION
                ),
                new ContinuousAccessAction(
                    ContinuousAccessAction::REVOKE_TOKENS
                ),
            ],
            ['risk elevated']
        );

        self::assertTrue($decision->requiresAction());
        self::assertCount(2, $decision->actions());
        self::assertSame(['risk elevated'], $decision->reasons());
    }

    public function testContextCanTargetSessionTokenAndClient(): void
    {
        $context = new ContinuousAccessContext(
            $this->subject(),
            'session-001',
            'token-001',
            'client-001'
        );

        self::assertSame('session-001', $context->sessionId());
        self::assertSame('token-001', $context->tokenId());
        self::assertSame('client-001', $context->clientId());
    }

    public function testExecutionResultSeparatesCompletedAndFailedActions(): void
    {
        $result = new ContinuousAccessExecutionResult(
            false,
            ['revoke-session'],
            ['revoke-tokens']
        );

        self::assertFalse($result->successful());
        self::assertSame(
            ['revoke-session'],
            $result->completedActions()
        );
        self::assertSame(
            ['revoke-tokens'],
            $result->failedActions()
        );
    }

    public function testDecisionAndExecutionContractsAreTyped(): void
    {
        $decision = new \ReflectionMethod(
            ContinuousAccessDecisionPolicyInterface::class,
            'decide'
        );
        $execution = new \ReflectionMethod(
            ContinuousAccessReactionExecutorInterface::class,
            'execute'
        );

        self::assertSame(
            ContinuousAccessDecision::class,
            (string) $decision->getReturnType()
        );
        self::assertSame(
            ContinuousAccessExecutionResult::class,
            (string) $execution->getReturnType()
        );
    }

    public function testConcreteReactionBoundariesRemainIndependent(): void
    {
        foreach ([
            SessionRevocationServiceInterface::class,
            TokenRevocationServiceInterface::class,
            ReauthenticationRequirementServiceInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testReactionLayerRemainsStorageAndTransportNeutral(): void
    {
        foreach ([
            ContinuousAccessDecisionPolicyInterface::class,
            ContinuousAccessReactionExecutorInterface::class,
            SessionRevocationServiceInterface::class,
            TokenRevocationServiceInterface::class,
            ReauthenticationRequirementServiceInterface::class,
            ContinuousAccessContext::class,
            ContinuousAccessDecision::class,
            ContinuousAccessExecutionResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'session_destroy',
                strtolower($source)
            );
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
