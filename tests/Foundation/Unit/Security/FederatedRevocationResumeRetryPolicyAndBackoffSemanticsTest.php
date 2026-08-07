<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationExecution;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationResumePlanner;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryAdvisor;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryPolicy;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationStep;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationStepResult;

final class FederatedRevocationResumeRetryPolicyAndBackoffSemanticsTest extends TestCase
{
    public function testResumePlanDoesNotRepeatSuccessfulSteps(): void
    {
        $previous = new FederatedRevocationExecution(
            $this->request(),
            [
                new FederatedRevocationStepResult(
                    FederatedRevocationStep::LocalSessions,
                    true,
                    true
                ),
                new FederatedRevocationStepResult(
                    FederatedRevocationStep::ProviderCredentials,
                    true,
                    false,
                    'provider_failure'
                ),
            ]
        );

        $plan = (new FederatedRevocationResumePlanner())->plan(
            $this->request(),
            $previous
        );

        self::assertSame(
            [
                FederatedRevocationStep::ProviderCredentials,
                FederatedRevocationStep::IdentityLink,
            ],
            $plan->remainingSteps()
        );
    }

    public function testFreshOperationPlansAllRequestedSteps(): void
    {
        $plan = (new FederatedRevocationResumePlanner())->plan(
            $this->request(),
            null
        );

        self::assertSame(
            [
                FederatedRevocationStep::LocalSessions,
                FederatedRevocationStep::ProviderCredentials,
                FederatedRevocationStep::IdentityLink,
            ],
            $plan->remainingSteps()
        );
    }

    public function testRetryPolicyUsesDeterministicExponentialBackoff(): void
    {
        $policy = new FederatedRevocationRetryPolicy(
            4,
            new DateInterval('PT10S')
        );

        self::assertSame(
            10,
            $this->seconds($policy->delayForAttempt(1))
        );
        self::assertSame(
            20,
            $this->seconds($policy->delayForAttempt(2))
        );
        self::assertSame(
            40,
            $this->seconds($policy->delayForAttempt(3))
        );
        self::assertSame(
            80,
            $this->seconds($policy->delayForAttempt(4))
        );
    }

    public function testRetryAdvisorComputesNextEligibilityWithoutSleeping(): void
    {
        $advisor = new FederatedRevocationRetryAdvisor(
            new FederatedRevocationRetryPolicy(
                3,
                new DateInterval('PT30S')
            )
        );

        $last = new DateTimeImmutable(
            '2026-08-07T19:30:00+00:00'
        );

        $assessment = $advisor->assess(1, $last);

        self::assertTrue($assessment->allowed());
        self::assertSame(
            '2026-08-07T19:31:00+00:00',
            $assessment
                ->state()
                ->nextEligibleAt()
                ?->format('Y-m-d\TH:i:sP')
        );
        self::assertFalse(
            $assessment->eligibleAt(
                $last->modify('+59 seconds')
            )
        );
        self::assertTrue(
            $assessment->eligibleAt(
                $last->modify('+60 seconds')
            )
        );
    }

    public function testRetryAdvisorDeniesAttemptsBeyondPolicy(): void
    {
        $advisor = new FederatedRevocationRetryAdvisor(
            new FederatedRevocationRetryPolicy(
                2,
                new DateInterval('PT10S')
            )
        );

        $assessment = $advisor->assess(
            2,
            new DateTimeImmutable(
                '2026-08-07T19:30:00+00:00'
            )
        );

        self::assertFalse($assessment->allowed());
        self::assertSame(
            'max_attempts_exhausted',
            $assessment->reason()
        );
    }

    public function testRetryFoundationDoesNotScheduleOrExecuteWorkAutomatically(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Operations/Revocation';

        foreach (glob($directory . '/*Retry*.php') ?: [] as $file) {
            $source = file_get_contents($file);

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
                'cron',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'schedule(',
                strtolower($source)
            );
        }
    }

    private function request(): FederatedRevocationRequest
    {
        return new FederatedRevocationRequest(
            new IdentityId('local-user-1'),
            new OidcFederatedIdentity(
                'https://identity.example',
                'subject-123'
            ),
            FederatedRevocationScope::All,
            new FederatedRevocationReason(
                'security.incident'
            )
        );
    }

    private function seconds(DateInterval $interval): int
    {
        $anchor = new DateTimeImmutable('@0');

        return $anchor->add($interval)->getTimestamp();
    }
}
