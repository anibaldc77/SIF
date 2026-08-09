<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AccessAssignmentProviderInterface;
use Sif\Foundation\Security\Contracts\AccessReviewDecisionPublisherInterface;
use Sif\Foundation\Security\Contracts\EntitlementCatalogInterface;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\AccessReviewDecision;
use Sif\Foundation\Security\Governance\AccessReviewItem;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

final class IdentityGovernanceSecurityAdministrationArchitectureTest extends TestCase
{
    public function testEntitlementIsExplicitAndMetadataIsTyped(): void
    {
        $entitlement = new Entitlement(
            new EntitlementId('finance.invoice.approve'),
            'Approve invoices',
            'finance',
            ['risk' => 'high']
        );

        self::assertSame(
            'finance.invoice.approve',
            $entitlement->id()->value()
        );
        self::assertSame('finance', $entitlement->resource());
        self::assertSame('high', $entitlement->metadata()['risk']);
    }

    public function testAssignmentModelsOptionalExpiration(): void
    {
        $assignment = new AccessAssignment(
            new GovernanceSubjectId('user-001'),
            new EntitlementId('finance.invoice.approve'),
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-09T10:00:00Z')
        );

        self::assertTrue(
            $assignment->activeAt(
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );
        self::assertFalse(
            $assignment->activeAt(
                new DateTimeImmutable('2026-08-10T12:00:00Z')
            )
        );
    }

    public function testAccessReviewDecisionIsExplicit(): void
    {
        $decision = new AccessReviewDecision(
            AccessReviewDecision::REVOKE,
            'Role no longer required.'
        );

        self::assertSame('revoke', $decision->value());
        self::assertSame(
            'Role no longer required.',
            $decision->reason()
        );
    }

    public function testReviewItemSeparatesAssignmentFromDecision(): void
    {
        $assignment = new AccessAssignment(
            new GovernanceSubjectId('user-001'),
            new EntitlementId('reports.read'),
            new DateTimeImmutable('2026-08-08T10:00:00Z')
        );

        $pending = new AccessReviewItem($assignment);

        self::assertFalse($pending->decided());

        $decided = new AccessReviewItem(
            $assignment,
            new AccessReviewDecision(
                AccessReviewDecision::APPROVE
            )
        );

        self::assertTrue($decided->decided());
    }

    public function testGovernanceContractsRemainStorageNeutral(): void
    {
        foreach ([
            EntitlementCatalogInterface::class,
            AccessAssignmentProviderInterface::class,
            AccessReviewDecisionPublisherInterface::class,
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

    public function testGovernanceFoundationRemainsProviderNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Governance';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('OneLogin', $source);
        }
    }
}
