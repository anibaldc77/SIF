<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticationRequestId;
use Sif\Foundation\Security\Contracts\CredentialInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Exceptions\InvalidAuthenticationRequestException;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;

final class AuthenticationContractsAndResultsTest extends TestCase
{
    public function testCredentialContractExposesOnlyItsStableType(): void
    {
        $credential = $this->credential('Password');

        self::assertSame('password', $credential->type()->value());
    }

    public function testInvalidCredentialTypeIsRejected(): void
    {
        $this->expectException(InvalidAuthenticationRequestException::class);

        new CredentialType('password secret');
    }

    public function testAuthenticationRequestMetadataNeverSerializesCredentialPayload(): void
    {
        $request = new AuthenticationRequest(
            new AuthenticationRequestId('request-42'),
            $this->credential('password'),
            new DateTimeImmutable('2026-08-05T08:30:00-03:00')
        );

        self::assertSame(
            [
                'request_id' => 'request-42',
                'credential_type' => 'password',
                'requested_at' => '2026-08-05T11:30:00.000000+00:00',
            ],
            $request->metadata()
        );
    }

    public function testSuccessfulResultContainsOnlyAuthenticatedPrincipal(): void
    {
        $principal = $this->principal();
        $result = AuthenticationResult::succeeded($principal);

        self::assertTrue($result->isSuccessful());
        self::assertSame($principal, $result->principal());
        self::assertNull($result->failure());
    }

    public function testFailedResultContainsOnlySanitizedFailureReason(): void
    {
        $result = AuthenticationResult::failed(AuthenticationFailureReason::InvalidCredentials);

        self::assertFalse($result->isSuccessful());
        self::assertNull($result->principal());
        self::assertSame(
            ['reason' => 'invalid_credentials'],
            $result->failure()?->toArray()
        );
    }

    private function credential(string $type): CredentialInterface
    {
        return new class ($type) implements CredentialInterface {
            public function __construct(private readonly string $type)
            {
            }

            public function type(): CredentialType
            {
                return new CredentialType($this->type);
            }
        };
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('tenant-a:user-42')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(20),
                new DateTimeImmutable('2026-08-05T11:30:00+00:00')
            )
        );
    }
}
