<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimBulkExecutorInterface;
use Sif\Foundation\Security\Contracts\ScimBulkValidatorInterface;
use Sif\Foundation\Security\Exceptions\InvalidScimBulkRequestException;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkId;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkIdMap;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkOperation;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkOperationResult;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkOperationType;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkRequest;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkResponse;

final class ScimBulkOperationsBulkIdResolutionAndFailureSemanticsTest extends TestCase
{
    public function testBulkRequestPreservesOperationOrderAndFailOnErrors(): void
    {
        $first = new ScimBulkOperation(
            new ScimBulkOperationType(ScimBulkOperationType::POST),
            '/Users',
            new ScimBulkId('user-1'),
            null,
            ['userName' => 'alice@example.com']
        );

        $second = new ScimBulkOperation(
            new ScimBulkOperationType(ScimBulkOperationType::DELETE),
            '/Users/old-user'
        );

        $request = new ScimBulkRequest(
            [ScimBulkRequest::SCHEMA_URI],
            [$first, $second],
            2
        );

        self::assertSame($first, $request->operations()[0]);
        self::assertSame($second, $request->operations()[1]);
        self::assertSame(2, $request->failOnErrors());
    }

    public function testBulkPostRequiresBulkId(): void
    {
        $this->expectException(
            InvalidScimBulkRequestException::class
        );

        new ScimBulkOperation(
            new ScimBulkOperationType(ScimBulkOperationType::POST),
            '/Users',
            null,
            null,
            ['userName' => 'alice@example.com']
        );
    }

    public function testWriteOperationsRequireData(): void
    {
        $this->expectException(
            InvalidScimBulkRequestException::class
        );

        new ScimBulkOperation(
            new ScimBulkOperationType(ScimBulkOperationType::PATCH),
            '/Users/user-1'
        );
    }

    public function testBulkIdsMustBeUnique(): void
    {
        $this->expectException(
            InvalidScimBulkRequestException::class
        );

        new ScimBulkRequest(
            [ScimBulkRequest::SCHEMA_URI],
            [
                new ScimBulkOperation(
                    new ScimBulkOperationType(
                        ScimBulkOperationType::POST
                    ),
                    '/Users',
                    new ScimBulkId('same-id'),
                    null,
                    ['userName' => 'alice@example.com']
                ),
                new ScimBulkOperation(
                    new ScimBulkOperationType(
                        ScimBulkOperationType::POST
                    ),
                    '/Users',
                    new ScimBulkId('same-id'),
                    null,
                    ['userName' => 'bob@example.com']
                ),
            ]
        );
    }

    public function testBulkIdMapResolvesPreviouslyRegisteredReferences(): void
    {
        $map = new ScimBulkIdMap();
        $id = new ScimBulkId('user-1');

        self::assertSame(
            'bulkId:user-1',
            $map->resolveReference('bulkId:user-1')
        );

        $map->register(
            $id,
            '/Users/81f3'
        );

        self::assertTrue($map->has($id));
        self::assertSame(
            '/Users/81f3',
            $map->resolveReference('bulkId:user-1')
        );
    }

    public function testBulkResponseAndContractsRemainInfrastructureNeutral(): void
    {
        $response = new ScimBulkResponse([
            new ScimBulkOperationResult(
                new ScimBulkOperationType(
                    ScimBulkOperationType::POST
                ),
                '201',
                '/Users/81f3',
                'W/"1"',
                new ScimBulkId('user-1')
            ),
        ]);

        self::assertCount(1, $response->operations());
        self::assertSame(
            '201',
            $response->operations()[0]->status()
        );

        foreach ([
            ScimBulkExecutorInterface::class,
            ScimBulkValidatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
        }
    }
}
