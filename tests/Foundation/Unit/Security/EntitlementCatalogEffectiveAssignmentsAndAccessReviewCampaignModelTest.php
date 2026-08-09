<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AccessAssignmentProviderInterface;
use Sif\Foundation\Security\Contracts\AccessReviewCampaignRepositoryInterface;
use Sif\Foundation\Security\Contracts\EntitlementCatalogInterface;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\AccessReviewCampaign;
use Sif\Foundation\Security\Governance\AccessReviewCampaignId;
use Sif\Foundation\Security\Governance\AccessReviewCampaignStatus;
use Sif\Foundation\Security\Governance\AccessReviewScope;
use Sif\Foundation\Security\Governance\DefaultEffectiveAccessAssignmentResolver;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

final class EntitlementCatalogEffectiveAssignmentsAndAccessReviewCampaignModelTest extends TestCase
{
    public function testEffectiveResolverIncludesOnlyActiveKnownAssignments(): void
    {
        $subject = new GovernanceSubjectId('user-001');
        $known = new Entitlement(
            new EntitlementId('reports.read'),
            'Read reports',
            'reports'
        );

        $resolver = new DefaultEffectiveAccessAssignmentResolver(
            new InMemoryAssignmentProvider([
                new AccessAssignment(
                    $subject,
                    new EntitlementId('reports.read'),
                    new DateTimeImmutable('2026-08-08T10:00:00Z')
                ),
                new AccessAssignment(
                    $subject,
                    new EntitlementId('expired.permission'),
                    new DateTimeImmutable('2026-08-01T10:00:00Z'),
                    new DateTimeImmutable('2026-08-02T10:00:00Z')
                ),
                new AccessAssignment(
                    $subject,
                    new EntitlementId('missing.permission'),
                    new DateTimeImmutable('2026-08-08T10:00:00Z')
                ),
            ]),
            new InMemoryEntitlementCatalog([$known])
        );

        $resolved = $resolver->resolve(
            $subject,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertCount(1, $resolved);
        self::assertSame(
            'reports.read',
            $resolved[0]->entitlement()->id()->value()
        );
    }

    public function testCampaignHasExplicitPeriodStatusAndScope(): void
    {
        $campaign = new AccessReviewCampaign(
            new AccessReviewCampaignId('campaign-2026-q3'),
            'Q3 privileged access review',
            new AccessReviewCampaignStatus(
                AccessReviewCampaignStatus::ACTIVE
            ),
            new AccessReviewScope(
                [new GovernanceSubjectId('user-001')],
                [new EntitlementId('finance.invoice.approve')]
            ),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z')
        );

        self::assertSame(
            'campaign-2026-q3',
            $campaign->id()->value()
        );
        self::assertFalse($campaign->scope()->empty());
        self::assertTrue(
            $campaign->activeAt(
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );
    }

    public function testClosedCampaignIsNotActiveInsideItsCalendarWindow(): void
    {
        $campaign = new AccessReviewCampaign(
            new AccessReviewCampaignId('closed-001'),
            'Closed review',
            new AccessReviewCampaignStatus(
                AccessReviewCampaignStatus::CLOSED
            ),
            new AccessReviewScope(),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z')
        );

        self::assertFalse(
            $campaign->activeAt(
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );
    }

    public function testScopeCanTargetSubjectsAndEntitlementsIndependently(): void
    {
        $subjectScope = new AccessReviewScope(
            [new GovernanceSubjectId('user-001')]
        );

        $entitlementScope = new AccessReviewScope(
            [],
            [new EntitlementId('reports.read')]
        );

        self::assertCount(1, $subjectScope->subjects());
        self::assertCount(1, $entitlementScope->entitlements());
    }

    public function testCampaignRepositoryRemainsStorageNeutral(): void
    {
        $reflection = new \ReflectionClass(
            AccessReviewCampaignRepositoryInterface::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
    }

    public function testResolverDoesNotPerformProvisioningOrAuthorization(): void
    {
        $reflection = new \ReflectionClass(
            DefaultEffectiveAccessAssignmentResolver::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Scim', $source);
        self::assertStringNotContainsString('AuthorizationDecision', $source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('Okta', $source);
    }
}

final readonly class InMemoryEntitlementCatalog implements EntitlementCatalogInterface
{
    /** @param list<Entitlement> $entitlements */
    public function __construct(private array $entitlements)
    {
    }

    public function find(EntitlementId $id): ?Entitlement
    {
        foreach ($this->entitlements as $entitlement) {
            if ($entitlement->id()->value() === $id->value()) {
                return $entitlement;
            }
        }

        return null;
    }

    public function all(): array
    {
        return $this->entitlements;
    }
}

final readonly class InMemoryAssignmentProvider implements AccessAssignmentProviderInterface
{
    /** @param list<AccessAssignment> $assignments */
    public function __construct(private array $assignments)
    {
    }

    public function forSubject(
        GovernanceSubjectId $subjectId
    ): array {
        return array_values(
            array_filter(
                $this->assignments,
                static fn (AccessAssignment $assignment): bool
                    => $assignment->subjectId()->value()
                    === $subjectId->value()
            )
        );
    }
}
