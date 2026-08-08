<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkId;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkIdMap;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkOperation;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkOperationType;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkRequest;
use Sif\Foundation\Security\Scim\Lifecycle\ScimLifecyclePlanner;
use Sif\Foundation\Security\Scim\Lifecycle\ScimLifecyclePolicy;
use Sif\Foundation\Security\Scim\Lifecycle\ScimProvisioningAction;
use Sif\Foundation\Security\Scim\Patch\ScimPatchOperation;
use Sif\Foundation\Security\Scim\Patch\ScimPatchOperationType;
use Sif\Foundation\Security\Scim\Patch\ScimPatchPath;
use Sif\Foundation\Security\Scim\Patch\ScimPatchRequest;
use Sif\Foundation\Security\Scim\Versioning\DefaultScimPreconditionEvaluator;
use Sif\Foundation\Security\Scim\Versioning\ScimEntityTag;
use Sif\Foundation\Security\Scim\Versioning\ScimPrecondition;
use Sif\Foundation\Security\Scim\Versioning\ScimResourceVersion;

final class Scim2IdentityProvisioningProductCompletionTest extends TestCase
{
    public function testPatchBulkVersioningAndLifecycleComposeWithoutInfrastructureCoupling(): void
    {
        $patch = new ScimPatchRequest(
            [ScimPatchRequest::SCHEMA_URI],
            [
                new ScimPatchOperation(
                    new ScimPatchOperationType(
                        ScimPatchOperationType::REPLACE
                    ),
                    new ScimPatchPath('active'),
                    false
                ),
            ]
        );

        self::assertCount(1, $patch->operations());

        $bulkId = new ScimBulkId('user-001');

        $bulk = new ScimBulkRequest(
            [ScimBulkRequest::SCHEMA_URI],
            [
                new ScimBulkOperation(
                    new ScimBulkOperationType(
                        ScimBulkOperationType::POST
                    ),
                    '/Users',
                    $bulkId,
                    null,
                    ['userName' => 'alice@example.com']
                ),
            ],
            1
        );

        self::assertSame(1, $bulk->failOnErrors());

        $map = new ScimBulkIdMap();
        $map->register($bulkId, '/Users/42');

        self::assertSame(
            '/Users/42',
            $map->resolveReference('bulkId:user-001')
        );

        $precondition = (new DefaultScimPreconditionEvaluator())
            ->evaluate(
                new ScimPrecondition(
                    ScimPrecondition::IF_MATCH,
                    [new ScimEntityTag('v2')]
                ),
                new ScimResourceVersion('v2')
            );

        self::assertTrue($precondition->satisfied());

        $plan = (new ScimLifecyclePlanner(
            new ScimLifecyclePolicy()
        ))->planUserDeletion();

        self::assertSame(
            ScimProvisioningAction::DEACTIVATE,
            $plan->actions()[0]->value()
        );
        self::assertSame(
            ScimProvisioningAction::DELETE,
            $plan->actions()[2]->value()
        );
    }

    public function testScimFoundationRemainsHttpStorageAndProviderNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Scim';

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
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('session_start', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
        }
    }

    public function testProductDoesNotOwnHttpStatusTranslation(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Scim';

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
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'header(',
                strtolower($source)
            );
        }
    }

    public function testProductKeepsMutationExecutionBehindContracts(): void
    {
        $contractsDirectory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Contracts';

        foreach ([
            'ScimPatchApplierInterface.php',
            'ScimBulkExecutorInterface.php',
            'ScimUserProvisionerInterface.php',
            'ScimGroupProvisionerInterface.php',
        ] as $fileName) {
            $path = $contractsDirectory . '/' . $fileName;
            self::assertFileExists($path);

            $source = file_get_contents($path);
            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testProductUsesOpaqueVersioningAndExplicitLifecyclePlanning(): void
    {
        $version = new ScimResourceVersion('opaque-revision-77');

        self::assertSame(
            'W/"opaque-revision-77"',
            $version->weakEtag()
        );

        $plan = (new ScimLifecyclePlanner(
            new ScimLifecyclePolicy(
                deactivateUserBeforeDelete: false,
                cleanupMembershipsBeforeDelete: true
            )
        ))->planUserDeletion();

        self::assertSame(
            [
                ScimProvisioningAction::REMOVE_MEMBERSHIP,
                ScimProvisioningAction::DELETE,
            ],
            array_map(
                static fn (ScimProvisioningAction $action): string
                    => $action->value(),
                $plan->actions()
            )
        );
    }

    public function testBulkAndPatchSchemasAreProtocolOwnedAndDeterministic(): void
    {
        self::assertSame(
            'urn:ietf:params:scim:api:messages:2.0:PatchOp',
            ScimPatchRequest::SCHEMA_URI
        );
        self::assertSame(
            'urn:ietf:params:scim:api:messages:2.0:BulkRequest',
            ScimBulkRequest::SCHEMA_URI
        );
    }
}
