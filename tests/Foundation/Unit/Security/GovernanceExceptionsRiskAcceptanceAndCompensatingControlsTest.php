<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CompensatingControlRepositoryInterface;
use Sif\Foundation\Security\Contracts\GovernanceExceptionApproverResolverInterface;
use Sif\Foundation\Security\Contracts\GovernanceExceptionRepositoryInterface;
use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\CompensatingControl;
use Sif\Foundation\Security\Governance\CompensatingControlId;
use Sif\Foundation\Security\Governance\ConflictRuleId;
use Sif\Foundation\Security\Governance\DefaultGovernanceExceptionEvaluator;
use Sif\Foundation\Security\Governance\GovernanceException;
use Sif\Foundation\Security\Governance\GovernanceExceptionDecision;
use Sif\Foundation\Security\Governance\GovernanceExceptionId;
use Sif\Foundation\Security\Governance\GovernanceExceptionStatus;
use Sif\Foundation\Security\Governance\GovernanceRiskLevel;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;
use Sif\Foundation\Security\Governance\RiskAcceptance;

final class GovernanceExceptionsRiskAcceptanceAndCompensatingControlsTest extends TestCase
{
    public function testApprovedExceptionIsEffectiveInsideItsWindow(): void
    {
        $exception = $this->exception(
            GovernanceExceptionStatus::APPROVED
        );

        self::assertTrue(
            (new DefaultGovernanceExceptionEvaluator())
                ->isEffective(
                    $exception,
                    new DateTimeImmutable(
                        '2026-08-08T12:00:00Z'
                    )
                )
        );
    }

    public function testRequestedExceptionIsNotEffective(): void
    {
        self::assertFalse(
            (new DefaultGovernanceExceptionEvaluator())
                ->isEffective(
                    $this->exception(
                        GovernanceExceptionStatus::REQUESTED
                    ),
                    new DateTimeImmutable(
                        '2026-08-08T12:00:00Z'
                    )
                )
        );
    }

    public function testExpiredRiskAcceptanceInvalidatesException(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $exception = new GovernanceException(
            new GovernanceExceptionId('exception-001'),
            $subject,
            new ConflictRuleId('rule-001'),
            new GovernanceExceptionStatus(
                GovernanceExceptionStatus::APPROVED
            ),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z'),
            new RiskAcceptance(
                $subject,
                new GovernanceRiskLevel(
                    GovernanceRiskLevel::HIGH
                ),
                'Temporary business need.',
                new DateTimeImmutable('2026-08-01T00:00:00Z'),
                new DateTimeImmutable('2026-08-05T00:00:00Z')
            )
        );

        self::assertFalse(
            (new DefaultGovernanceExceptionEvaluator())
                ->isEffective(
                    $exception,
                    new DateTimeImmutable(
                        '2026-08-08T12:00:00Z'
                    )
                )
        );
    }

    public function testCompensatingControlCarriesResidualRisk(): void
    {
        $control = new CompensatingControl(
            new CompensatingControlId('control-001'),
            'Dual approval monitoring',
            'Independent review of all approved payments.',
            new GovernanceRiskLevel(
                GovernanceRiskLevel::MEDIUM
            )
        );

        self::assertSame(
            'control-001',
            $control->id()->value()
        );
        self::assertSame(
            GovernanceRiskLevel::MEDIUM,
            $control->residualRisk()->value()
        );
    }

    public function testExceptionDecisionKeepsApproverAndOutcomeExplicit(): void
    {
        $decision = new GovernanceExceptionDecision(
            new GovernanceExceptionStatus(
                GovernanceExceptionStatus::APPROVED
            ),
            new AccessReviewerId('risk-owner-001'),
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            'Compensating control accepted.'
        );

        self::assertSame(
            GovernanceExceptionStatus::APPROVED,
            $decision->status()->value()
        );
        self::assertSame(
            'risk-owner-001',
            $decision->approverId()->value()
        );
    }

    public function testExceptionContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            GovernanceExceptionRepositoryInterface::class,
            CompensatingControlRepositoryInterface::class,
            GovernanceExceptionApproverResolverInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Scim', $source);
        }
    }

    private function exception(string $status): GovernanceException
    {
        $subject = new GovernanceSubjectId('user-001');

        return new GovernanceException(
            new GovernanceExceptionId('exception-001'),
            $subject,
            new ConflictRuleId('rule-001'),
            new GovernanceExceptionStatus($status),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z'),
            new RiskAcceptance(
                $subject,
                new GovernanceRiskLevel(
                    GovernanceRiskLevel::HIGH
                ),
                'Temporary business need.',
                new DateTimeImmutable('2026-08-01T00:00:00Z'),
                new DateTimeImmutable('2026-08-20T00:00:00Z')
            ),
            [
                new CompensatingControl(
                    new CompensatingControlId('control-001'),
                    'Independent review',
                    'Secondary review of conflicting transactions.',
                    new GovernanceRiskLevel(
                        GovernanceRiskLevel::MEDIUM
                    )
                ),
            ]
        );
    }
}
