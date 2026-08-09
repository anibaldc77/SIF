<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\EffectiveAccessAssignmentResolverInterface;
use Sif\Foundation\Security\Contracts\SegregationOfDutiesRuleProviderInterface;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\ConflictRuleId;
use Sif\Foundation\Security\Governance\DefaultGovernanceRiskEvaluator;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceRiskLevel;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;
use Sif\Foundation\Security\Governance\SegregationOfDutiesRule;

final class SegregationOfDutiesConflictRulesAndGovernanceRiskEvaluationTest extends TestCase
{
    public function testConflictRuleMatchesBothEntitlementOrders(): void
    {
        $rule = $this->rule();

        self::assertTrue(
            $rule->conflicts(
                new EntitlementId('payments.create'),
                new EntitlementId('payments.approve')
            )
        );
        self::assertTrue(
            $rule->conflicts(
                new EntitlementId('payments.approve'),
                new EntitlementId('payments.create')
            )
        );
    }

    public function testRiskLevelHasDeterministicWeight(): void
    {
        self::assertSame(
            100,
            (new GovernanceRiskLevel(
                GovernanceRiskLevel::CRITICAL
            ))->weight()
        );
        self::assertSame(
            30,
            (new GovernanceRiskLevel(
                GovernanceRiskLevel::MEDIUM
            ))->weight()
        );
    }

    public function testEvaluatorFindsConflictForEffectiveAssignments(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $evaluator = new DefaultGovernanceRiskEvaluator(
            new StaticRiskAssignmentResolver([
                $this->assignment(
                    $subject,
                    'payments.create'
                ),
                $this->assignment(
                    $subject,
                    'payments.approve'
                ),
            ]),
            new StaticSodRuleProvider([
                $this->rule(),
            ])
        );

        $assessment = $evaluator->evaluate(
            $subject,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertTrue($assessment->hasConflicts());
        self::assertCount(1, $assessment->conflicts());
        self::assertSame(100, $assessment->score());
    }

    public function testEvaluatorReturnsNoConflictWhenCombinationIsSafe(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $evaluator = new DefaultGovernanceRiskEvaluator(
            new StaticRiskAssignmentResolver([
                $this->assignment(
                    $subject,
                    'reports.read'
                ),
                $this->assignment(
                    $subject,
                    'payments.approve'
                ),
            ]),
            new StaticSodRuleProvider([
                $this->rule(),
            ])
        );

        $assessment = $evaluator->evaluate(
            $subject,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertFalse($assessment->hasConflicts());
        self::assertSame(0, $assessment->score());
    }

    public function testRiskScoreIsCappedAtOneHundred(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $critical = $this->rule();
        $high = new SegregationOfDutiesRule(
            new ConflictRuleId('rule-high'),
            new EntitlementId('reports.read'),
            new EntitlementId('reports.export'),
            new GovernanceRiskLevel(
                GovernanceRiskLevel::HIGH
            ),
            'Read/export combination.'
        );

        $evaluator = new DefaultGovernanceRiskEvaluator(
            new StaticRiskAssignmentResolver([
                $this->assignment($subject, 'payments.create'),
                $this->assignment($subject, 'payments.approve'),
                $this->assignment($subject, 'reports.read'),
                $this->assignment($subject, 'reports.export'),
            ]),
            new StaticSodRuleProvider([$critical, $high])
        );

        self::assertSame(
            100,
            $evaluator->evaluate(
                $subject,
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )->score()
        );
    }

    public function testRiskContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            SegregationOfDutiesRuleProviderInterface::class,
            DefaultGovernanceRiskEvaluator::class,
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

    private function rule(): SegregationOfDutiesRule
    {
        return new SegregationOfDutiesRule(
            new ConflictRuleId('payments-create-approve'),
            new EntitlementId('payments.create'),
            new EntitlementId('payments.approve'),
            new GovernanceRiskLevel(
                GovernanceRiskLevel::CRITICAL
            ),
            'A subject must not create and approve payments.'
        );
    }

    private function assignment(
        GovernanceSubjectId $subject,
        string $entitlement
    ): EffectiveAccessAssignment {
        $id = new EntitlementId($entitlement);

        return new EffectiveAccessAssignment(
            new AccessAssignment(
                $subject,
                $id,
                new DateTimeImmutable('2026-08-01T00:00:00Z')
            ),
            new Entitlement(
                $id,
                $entitlement,
                'application'
            )
        );
    }
}

final readonly class StaticSodRuleProvider implements SegregationOfDutiesRuleProviderInterface
{
    /**
     * @param list<SegregationOfDutiesRule> $rules
     */
    public function __construct(private array $rules)
    {
    }

    public function all(): array
    {
        return $this->rules;
    }
}

final readonly class StaticRiskAssignmentResolver implements EffectiveAccessAssignmentResolverInterface
{
    /**
     * @param list<EffectiveAccessAssignment> $assignments
     */
    public function __construct(private array $assignments)
    {
    }

    public function resolve(
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): array {
        return array_values(
            array_filter(
                $this->assignments,
                static fn (
                    EffectiveAccessAssignment $assignment
                ): bool => $assignment
                    ->assignment()
                    ->subjectId()
                    ->value()
                    === $subjectId->value()
            )
        );
    }
}
