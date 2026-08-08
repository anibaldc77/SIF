<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimPatchApplierInterface;
use Sif\Foundation\Security\Contracts\ScimPatchValidatorInterface;
use Sif\Foundation\Security\Exceptions\InvalidScimPatchException;
use Sif\Foundation\Security\Scim\Patch\ScimPatchOperation;
use Sif\Foundation\Security\Scim\Patch\ScimPatchOperationType;
use Sif\Foundation\Security\Scim\Patch\ScimPatchPath;
use Sif\Foundation\Security\Scim\Patch\ScimPatchRequest;

final class ScimPatchOperationsPathSemanticsAndMutationContractsTest extends TestCase
{
    public function testAddReplaceAndRemoveOperationsAreExplicit(): void
    {
        $add = new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::ADD),
            new ScimPatchPath('emails'),
            [['value' => 'alice@example.com']]
        );

        $replace = new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REPLACE),
            new ScimPatchPath('active'),
            false
        );

        $remove = new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REMOVE),
            new ScimPatchPath('phoneNumbers[type eq "work"]')
        );

        self::assertSame('add', $add->operation()->value());
        self::assertSame('replace', $replace->operation()->value());
        self::assertSame('remove', $remove->operation()->value());
        self::assertSame(
            'phoneNumbers[type eq "work"]',
            $remove->path()?->value()
        );
    }

    public function testPatchRequestPreservesOperationOrder(): void
    {
        $first = new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REPLACE),
            new ScimPatchPath('active'),
            false
        );

        $second = new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REMOVE),
            new ScimPatchPath('nickName')
        );

        $request = new ScimPatchRequest(
            [ScimPatchRequest::SCHEMA_URI],
            [$first, $second]
        );

        self::assertSame($first, $request->operations()[0]);
        self::assertSame($second, $request->operations()[1]);
    }

    public function testRemoveRequiresPath(): void
    {
        $this->expectException(InvalidScimPatchException::class);

        new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REMOVE)
        );
    }

    public function testAddAndReplaceRequireValue(): void
    {
        $this->expectException(InvalidScimPatchException::class);

        new ScimPatchOperation(
            new ScimPatchOperationType(ScimPatchOperationType::REPLACE),
            new ScimPatchPath('displayName')
        );
    }

    public function testPatchRequestRequiresPatchOpSchemaAndOperations(): void
    {
        $this->expectException(InvalidScimPatchException::class);

        new ScimPatchRequest(
            ['urn:ietf:params:scim:schemas:core:2.0:User'],
            [
                new ScimPatchOperation(
                    new ScimPatchOperationType(
                        ScimPatchOperationType::REPLACE
                    ),
                    new ScimPatchPath('active'),
                    true
                ),
            ]
        );
    }

    public function testPatchContractsRemainStorageTransportAndProviderNeutral(): void
    {
        foreach ([
            ScimPatchApplierInterface::class,
            ScimPatchValidatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SQL', strtoupper($source));
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
        }
    }
}
