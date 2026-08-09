<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\AccessReviewCampaign;
use Sif\Foundation\Security\Governance\AccessReviewCampaignId;
use Sif\Foundation\Security\Governance\AccessReviewCampaignStatus;
use Sif\Foundation\Security\Governance\AccessReviewScope;
use Sif\Foundation\Security\Governance\AccessReviewWorkflowStatus;
use Sif\Foundation\Security\Governance\AccessReviewWorkItem;
use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\CompensatingControl;
use Sif\Foundation\Security\Governance\CompensatingControlId;
use Sif\Foundation\Security\Governance\ConflictRuleId;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;
use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;
use Sif\Foundation\Security\Governance\GovernanceException;
use Sif\Foundation\Security\Governance\GovernanceExceptionId;
use Sif\Foundation\Security\Governance\GovernanceExceptionStatus;
use Sif\Foundation\Security\Governance\GovernanceRiskLevel;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;
use Sif\Foundation\Security\Governance\RiskAcceptance;
use Sif\Foundation\Security\Governance\SegregationOfDutiesRule;

final class IdentityGovernanceSecurityAdministrationProductCompletionTest extends TestCase
{
    public function testGovernanceDomainComposesAcrossEntitlementsReviewsRiskAndExceptions(): void
    {
        $subject = new GovernanceSubjectId('user-001');
        $entitlementId = new EntitlementId('payments.approve');

        $effective = new EffectiveAccessAssignment(
            new AccessAssignment(
                $subject,
                $entitlementId,
                new DateTimeImmutable('2026-08-01T00:00:00Z')
            ),
            new Entitlement(
                $entitlementId,
                'Approve payments',
                'payments',
                ['risk' => 'critical']
            )
        );

        $campaign = new AccessReviewCampaign(
            new AccessReviewCampaignId('campaign-001'),
            'Quarterly review',
            new AccessReviewCampaignStatus(
                AccessReviewCampaignStatus::ACTIVE
            ),
            new AccessReviewScope(
                [$subject],
                [$entitlementId]
            ),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-09-01T00:00:00Z')
        );

        $item = new AccessReviewWorkItem(
            $campaign->id(),
            $effective,
            new AccessReviewerId('manager-001'),
            new AccessReviewWorkflowStatus(
                AccessReviewWorkflowStatus::PENDING
            )
        );

        $rule = new SegregationOfDutiesRule(
            new ConflictRuleId('payments-create-approve'),
            new EntitlementId('payments.create'),
            $entitlementId,
            new GovernanceRiskLevel(
                GovernanceRiskLevel::CRITICAL
            ),
            'Create and approve payments must remain separated.'
        );

        $exception = new GovernanceException(
            new GovernanceExceptionId('exception-001'),
            $subject,
            $rule->id(),
            new GovernanceExceptionStatus(
                GovernanceExceptionStatus::APPROVED
            ),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-08-31T00:00:00Z'),
            new RiskAcceptance(
                $subject,
                new GovernanceRiskLevel(
                    GovernanceRiskLevel::HIGH
                ),
                'Temporary operational need.',
                new DateTimeImmutable('2026-08-01T00:00:00Z'),
                new DateTimeImmutable('2026-08-20T00:00:00Z')
            ),
            [
                new CompensatingControl(
                    new CompensatingControlId('control-001'),
                    'Independent monitoring',
                    'Secondary review of payment approvals.',
                    new GovernanceRiskLevel(
                        GovernanceRiskLevel::MEDIUM
                    )
                ),
            ]
        );

        self::assertSame('user-001', $item->effectiveAssignment()->assignment()->subjectId()->value());
        self::assertSame('manager-001', $item->reviewerId()->value());
        self::assertSame('payments-create-approve', $rule->id()->value());
        self::assertSame(
            GovernanceExceptionStatus::APPROVED,
            $exception->status()->value()
        );
        self::assertCount(1, $exception->controls());
    }

    public function testGovernanceFoundationRemainsInfrastructureAndProviderNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Governance';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('Microsoft', $source);
        }
    }

    public function testGovernanceContractsDoNotOwnRemediationExecutionDetails(): void
    {
        $contracts = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Contracts';

        foreach ([
            'GovernanceRemediationPlannerInterface.php',
            'RemediationPlanRepositoryInterface.php',
            'GovernanceExpirationProcessorInterface.php',
            'GovernanceEventPublisherInterface.php',
        ] as $fileName) {
            $path = $contracts . '/' . $fileName;

            self::assertFileExists($path);

            $source = file_get_contents($path);

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testGovernanceDoesNotDuplicateAuthorizationOrScimResponsibilities(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Governance';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('ScimUserProvisioner', $source);
            self::assertStringNotContainsString('AuthorizationDecisionEngine', $source);
        }
    }

    public function testProductCompletionKeepsReviewRiskAndExceptionConceptsDistinct(): void
    {
        self::assertTrue(class_exists(
            AccessReviewWorkItem::class
        ));
        self::assertTrue(class_exists(
            SegregationOfDutiesRule::class
        ));
        self::assertTrue(class_exists(
            GovernanceException::class
        ));
        self::assertTrue(interface_exists(
            \Sif\Foundation\Security\Contracts\GovernanceEventPublisherInterface::class
        ));
    }

    public function testProductCompletionRetainsExplicitTemporalSemantics(): void
    {
        $assignment = new AccessAssignment(
            new GovernanceSubjectId('user-001'),
            new EntitlementId('reports.read'),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
            new DateTimeImmutable('2026-08-31T00:00:00Z')
        );

        self::assertTrue(
            $assignment->activeAt(
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );

        self::assertFalse(
            $assignment->activeAt(
                new DateTimeImmutable('2026-09-01T00:00:00Z')
            )
        );
    }
}
