<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AccessReviewEscalationResolverInterface;
use Sif\Foundation\Security\Exceptions\InvalidAccessReviewWorkflowTransitionException;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\AccessReviewCampaignId;
use Sif\Foundation\Security\Governance\AccessReviewDeadline;
use Sif\Foundation\Security\Governance\AccessReviewDelegation;
use Sif\Foundation\Security\Governance\AccessReviewEscalation;
use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\AccessReviewWorkflowManager;
use Sif\Foundation\Security\Governance\AccessReviewWorkflowStatus;
use Sif\Foundation\Security\Governance\AccessReviewWorkItem;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

final class AccessReviewWorkflowTransitionsDeadlinesEscalationAndDelegationModelTest extends TestCase
{
    public function testPendingItemCanEnterReview(): void
    {
        $item = $this->item(
            AccessReviewWorkflowStatus::PENDING
        );

        $transition = (new AccessReviewWorkflowManager())->start(
            $item,
            new AccessReviewerId('reviewer-001'),
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertSame(
            AccessReviewWorkflowStatus::PENDING,
            $transition->from()->value()
        );
        self::assertSame(
            AccessReviewWorkflowStatus::IN_REVIEW,
            $transition->to()->value()
        );
    }

    public function testOnlyInReviewItemCanBeDecided(): void
    {
        $item = $this->item(
            AccessReviewWorkflowStatus::IN_REVIEW
        );

        $transition = (new AccessReviewWorkflowManager())->decide(
            $item,
            new AccessReviewerId('reviewer-001'),
            new DateTimeImmutable('2026-08-08T13:00:00Z')
        );

        self::assertSame(
            AccessReviewWorkflowStatus::DECIDED,
            $transition->to()->value()
        );
    }

    public function testInvalidTransitionIsRejected(): void
    {
        $this->expectException(
            InvalidAccessReviewWorkflowTransitionException::class
        );

        (new AccessReviewWorkflowManager())->decide(
            $this->item(
                AccessReviewWorkflowStatus::PENDING
            ),
            new AccessReviewerId('reviewer-001'),
            new DateTimeImmutable('2026-08-08T13:00:00Z')
        );
    }

    public function testDeadlineDetectsOverdueState(): void
    {
        $deadline = new AccessReviewDeadline(
            new DateTimeImmutable('2026-08-08T18:00:00Z')
        );

        self::assertFalse(
            $deadline->overdueAt(
                new DateTimeImmutable('2026-08-08T17:59:59Z')
            )
        );
        self::assertTrue(
            $deadline->overdueAt(
                new DateTimeImmutable('2026-08-08T18:00:00Z')
            )
        );
    }

    public function testDelegationAndEscalationAreExplicitAndImmutable(): void
    {
        $from = new AccessReviewerId('reviewer-001');
        $to = new AccessReviewerId('reviewer-002');

        $delegation = new AccessReviewDelegation(
            $from,
            $to,
            new DateTimeImmutable('2026-08-08T14:00:00Z'),
            'Vacation coverage'
        );

        $escalation = new AccessReviewEscalation(
            $from,
            $to,
            new DateTimeImmutable('2026-08-08T18:30:00Z'),
            'Deadline exceeded'
        );

        self::assertSame(
            'reviewer-002',
            $delegation->toReviewer()->value()
        );
        self::assertSame(
            'Deadline exceeded',
            $escalation->reason()
        );
    }

    public function testWorkflowAndEscalationRemainInfrastructureNeutral(): void
    {
        foreach ([
            AccessReviewEscalationResolverInterface::class,
            AccessReviewWorkflowManager::class,
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

    private function item(string $status): AccessReviewWorkItem
    {
        $id = new EntitlementId('reports.read');

        return new AccessReviewWorkItem(
            new AccessReviewCampaignId('campaign-001'),
            new EffectiveAccessAssignment(
                new AccessAssignment(
                    new GovernanceSubjectId('user-001'),
                    $id,
                    new DateTimeImmutable('2026-08-01T00:00:00Z')
                ),
                new Entitlement(
                    $id,
                    'Read reports',
                    'reports'
                )
            ),
            new AccessReviewerId('reviewer-001'),
            new AccessReviewWorkflowStatus($status)
        );
    }
}
