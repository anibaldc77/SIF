<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authentication\AuthenticationState;
use Sif\Foundation\Security\Exceptions\InvalidIdentityException;
use Sif\Foundation\Security\Exceptions\InvalidPrincipalAttributeException;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttribute;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;

final class ImmutableIdentityPrincipalModelTest extends TestCase
{
    public function testIdentityIdentifierIsOpaqueAndComparedByValue(): void
    {
        $first = new IdentityId('tenant-a:user-42');
        $second = new IdentityId('tenant-a:user-42');
        $other = new IdentityId('tenant-a:user-43');

        self::assertSame('tenant-a:user-42', $first->value());
        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($other));
    }

    public function testIdentityIdentifierRejectsControlCharacters(): void
    {
        $this->expectException(InvalidIdentityException::class);

        new IdentityId("user\n42");
    }

    public function testPrincipalAttributesAreUniqueAndCanonicallyOrdered(): void
    {
        $attributes = new PrincipalAttributeCollection(
            new PrincipalAttribute('tenant.id', 'tenant-a'),
            new PrincipalAttribute('account.enabled', true),
            new PrincipalAttribute('risk.score', 7)
        );

        self::assertSame(
            [
                'account.enabled' => true,
                'risk.score' => 7,
                'tenant.id' => 'tenant-a',
            ],
            $attributes->toArray()
        );
        self::assertTrue($attributes->has('tenant.id'));
        self::assertSame('tenant-a', $attributes->get('tenant.id')?->value());
        self::assertCount(3, $attributes);
    }

    public function testDuplicatePrincipalAttributesFailClosed(): void
    {
        $this->expectException(InvalidPrincipalAttributeException::class);

        new PrincipalAttributeCollection(
            new PrincipalAttribute('tenant.id', 'tenant-a'),
            new PrincipalAttribute('tenant.id', 'tenant-b')
        );
    }

    public function testAuthenticatedPrincipalExposesDeterministicNonSensitiveSnapshot(): void
    {
        $principal = new AuthenticatedPrincipal(
            new Identity(new IdentityId('tenant-a:user-42')),
            new PrincipalAttributeCollection(
                new PrincipalAttribute('display.name', 'Example User'),
                new PrincipalAttribute('tenant.id', 'tenant-a')
            ),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(20),
                new DateTimeImmutable('2026-08-05T10:15:30.123456-03:00')
            )
        );

        self::assertSame(AuthenticationState::Authenticated, $principal->authenticationState());
        self::assertTrue($principal->isAuthenticated());
        self::assertSame('tenant-a:user-42', $principal->identity()->id()->value());
        self::assertSame('password', $principal->evidence()->method()->value());
        self::assertTrue($principal->evidence()->level()->satisfies(new AuthenticationLevel(10)));
        self::assertSame(
            [
                'identity_id' => 'tenant-a:user-42',
                'attributes' => [
                    'display.name' => 'Example User',
                    'tenant.id' => 'tenant-a',
                ],
                'authentication' => [
                    'method' => 'password',
                    'level' => 20,
                    'authenticated_at' => '2026-08-05T13:15:30.123+00:00',
                ],
            ],
            $principal->toArray()
        );
    }
}
