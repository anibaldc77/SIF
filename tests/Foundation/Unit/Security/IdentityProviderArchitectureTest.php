<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Exceptions\InvalidIdentityLookupException;
use Sif\Foundation\Security\Exceptions\InvalidIdentityProviderResultException;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderRecord;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;

final class IdentityProviderArchitectureTest extends TestCase
{
    public function testProviderIdentifiersAreStableAndComparable(): void
    {
        $first = new IdentityProviderId(' Local.Users ');
        $second = new IdentityProviderId('local.users');

        self::assertSame('local.users', $first->value());
        self::assertTrue($first->equals($second));
    }

    public function testLookupKeysPreserveProviderOwnedSemantics(): void
    {
        $lookup = new IdentityLookupKey(' User.Name@example.test ');

        self::assertSame('User.Name@example.test', $lookup->value());

        $this->expectException(InvalidIdentityLookupException::class);
        new IdentityLookupKey("invalid\nidentifier");
    }

    public function testProviderResultsRepresentFoundAndNotFoundWithoutNullContracts(): void
    {
        $record = new IdentityProviderRecord(
            new Identity(new IdentityId('identity-1001')),
            IdentityAccountStatus::Active
        );
        $found = IdentityProviderResult::found($record);
        $missing = IdentityProviderResult::notFound();

        self::assertTrue($found->wasFound());
        self::assertSame($record, $found->record());
        self::assertTrue($record->isActive());
        self::assertFalse($missing->wasFound());

        $this->expectException(InvalidIdentityProviderResultException::class);
        $missing->record();
    }

    public function testProviderContractDoesNotDependOnPersistenceOrExternalProtocols(): void
    {
        $reflection = new \ReflectionClass(IdentityProviderInterface::class);
        $source = file_get_contents((string) $reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('BaseModel', $source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('OpenId', $source);
        self::assertStringNotContainsString('Jwt', $source);
    }
}
