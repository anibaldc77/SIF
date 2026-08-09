<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AccessReviewerResolverInterface;
use Sif\Foundation\Security\Contracts\AccessReviewWorkItemRepositoryInterface;
use Sif\Foundation\Security\Contracts\EffectiveAccessAssignmentResolverInterface;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\AccessReviewCampaign;
use Sif\Foundation\Security\Governance\AccessReviewCampaignId;
use Sif\Foundation\Security\Governance\AccessReviewCampaignStatus;
use Sif\Foundation\Security\Governance\AccessReviewDecision;
use Sif\Foundation\Security\Governance\AccessReviewScope;
use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\AccessReviewWorkflowStatus;
use Sif\Foundation\Security\Governance\AccessReviewWorkItem;
use Sif\Foundation\Security\Governance\AccessReviewWorkItemFactory;
use Sif\Foundation\Security\Governance\AccessReviewWorkItemGenerator;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

final class AccessReviewItemGenerationReviewerAssignmentAndWorkflowStateModelTest extends TestCase
{
    public function testGeneratorCreatesPendingItemForInScopeEffectiveAssignment(): void
    {
        $subject = new GovernanceSubjectId('user-001');
        $assignment = $this->effectiveAssignment(
            $subject,
            'finance.invoice.approve'
        );

        $generator = new AccessReviewWorkItemGenerator(
            new StaticEffectiveResolver([$assignment]),
            new AccessReviewWorkItemFactory(
                new StaticReviewerResolver(
                    new AccessReviewerId('manager-001')
                )
            )
        );

        $items = $generator->generate(
            $this->activeCampaign(
                new AccessReviewScope(
                    [$subject],
                    [new EntitlementId('finance.invoice.approve')]
                )
            ),
            $subject,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertCount(1, $items);
        self::assertSame(
            'manager-001',
            $items[0]->reviewerId()->value()
        );
        self::assertSame(
            AccessReviewWorkflowStatus::PENDING,
            $items[0]->status()->value()
        );
    }

    public function testGeneratorSkipsAssignmentsOutsideCampaignScope(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $generator = new AccessReviewWorkItemGenerator(
            new StaticEffectiveResolver([
                $this->effectiveAssignment(
                    $subject,
                    'reports.read'
                ),
            ]),
            new AccessReviewWorkItemFactory(
                new StaticReviewerResolver(
                    new AccessReviewerId('manager-001')
                )
            )
        );

        $items = $generator->generate(
            $this->activeCampaign(
                new AccessReviewScope(
                    [$subject],
                    [new EntitlementId('finance.invoice.approve')]
                )
            ),
            $subject,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertSame([], $items);
    }

    public function testInactiveCampaignProducesNoWorkItems(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $campaign = new AccessReviewCampaign(
            new AccessReviewCampaignId('campaign-closed'),
            'Closed campaign',
            new AccessReviewCampaignStatus(
                AccessReviewCampaignStatus::CLOSED
            ),
            new AccessReviewScope(),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z')
        );

        $generator = new AccessReviewWorkItemGenerator(
            new StaticEffectiveResolver([
                $this->effectiveAssignment(
                    $subject,
                    'reports.read'
                ),
            ]),
            new AccessReviewWorkItemFactory(
                new StaticReviewerResolver(
                    new AccessReviewerId('manager-001')
                )
            )
        );

        self::assertSame(
            [],
            $generator->generate(
                $campaign,
                $subject,
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );
    }

    public function testWorkItemSeparatesWorkflowStatusFromDecision(): void
    {
        $subject = new GovernanceSubjectId('user-001');

        $item = new AccessReviewWorkItem(
            new AccessReviewCampaignId('campaign-001'),
            $this->effectiveAssignment(
                $subject,
                'reports.read'
            ),
            new AccessReviewerId('manager-001'),
            new AccessReviewWorkflowStatus(
                AccessReviewWorkflowStatus::DECIDED
            ),
            new AccessReviewDecision(
                AccessReviewDecision::APPROVE
            )
        );

        self::assertTrue($item->decided());
        self::assertSame(
            AccessReviewDecision::APPROVE,
            $item->decision()?->value()
        );
    }

    public function testReviewerAndWorkItemContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            AccessReviewerResolverInterface::class,
            AccessReviewWorkItemRepositoryInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testGeneratorDoesNotPerformRemediation(): void
    {
        $reflection = new \ReflectionClass(
            AccessReviewWorkItemGenerator::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Scim', $source);
        self::assertStringNotContainsString('revoke(', strtolower($source));
        self::assertStringNotContainsString('delete(', strtolower($source));
    }

    private function activeCampaign(
        AccessReviewScope $scope
    ): AccessReviewCampaign {
        return new AccessReviewCampaign(
            new AccessReviewCampaignId('campaign-001'),
            'Quarterly review',
            new AccessReviewCampaignStatus(
                AccessReviewCampaignStatus::ACTIVE
            ),
            $scope,
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z')
        );
    }

    private function effectiveAssignment(
        GovernanceSubjectId $subject,
        string $entitlementId
    ): EffectiveAccessAssignment {
        $id = new EntitlementId($entitlementId);

        return new EffectiveAccessAssignment(
            new AccessAssignment(
                $subject,
                $id,
                new DateTimeImmutable('2026-08-01T00:00:00Z')
            ),
            new Entitlement(
                $id,
                $entitlementId,
                'application'
            )
        );
    }
}

final readonly class StaticReviewerResolver implements AccessReviewerResolverInterface
{
    public function __construct(
        private AccessReviewerId $reviewer
    ) {
    }

    public function resolve(
        AccessReviewCampaign $campaign,
        EffectiveAccessAssignment $assignment
    ): AccessReviewerId {
        return $this->reviewer;
    }
}

final readonly class StaticEffectiveResolver implements EffectiveAccessAssignmentResolverInterface
{
    /**
     * @param list<EffectiveAccessAssignment> $assignments
     */
    public function __construct(
        private array $assignments
    ) {
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
