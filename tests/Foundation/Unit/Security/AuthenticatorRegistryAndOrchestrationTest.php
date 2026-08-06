<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Authentication\AuthenticationOrchestrator;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticationRequestId;
use Sif\Foundation\Security\Authentication\AuthenticatorId;
use Sif\Foundation\Security\Authentication\AuthenticatorRegistry;
use Sif\Foundation\Security\Contracts\AuthenticationTechnicalFailureHandlerInterface;
use Sif\Foundation\Security\Contracts\AuthenticatorInterface;
use Sif\Foundation\Security\Contracts\CredentialInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Exceptions\AmbiguousCredentialTypeException;
use Sif\Foundation\Security\Exceptions\DuplicateAuthenticatorException;
use Sif\Foundation\Security\Exceptions\InvalidAuthenticatorException;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;
use Throwable;

final class AuthenticatorRegistryAndOrchestrationTest extends TestCase
{
    public function testRegistryResolvesAuthenticatorDeterministicallyByCredentialType(): void
    {
        $password = $this->authenticator('password-authenticator', ['password']);
        $apiKey = $this->authenticator('api-key-authenticator', ['api_key']);
        $registry = new AuthenticatorRegistry();

        $registry->register($password);
        $registry->register($apiKey);

        self::assertSame($password, $registry->findFor(new CredentialType('password')));
        self::assertSame($apiKey, $registry->findFor(new CredentialType('api_key')));
        self::assertSame([$password, $apiKey], $registry->all());
    }

    public function testRegistryRejectsDuplicateIdsAndAmbiguousCredentialOwnership(): void
    {
        $registry = new AuthenticatorRegistry();
        $registry->register($this->authenticator('primary', ['password']));

        try {
            $registry->register($this->authenticator('primary', ['api_key']));
            self::fail('Duplicate authenticator identifiers must be rejected.');
        } catch (DuplicateAuthenticatorException) {
            self::assertCount(1, $registry->all());
        }

        $this->expectException(AmbiguousCredentialTypeException::class);
        $registry->register($this->authenticator('secondary', ['password']));
    }

    public function testRegistryRejectsAuthenticatorWithoutCredentialTypes(): void
    {
        $registry = new AuthenticatorRegistry();
        $authenticator = new class implements AuthenticatorInterface {
            public function id(): AuthenticatorId
            {
                return new AuthenticatorId('empty-authenticator');
            }

            public function supportedCredentialTypes(): array
            {
                return [];
            }

            public function authenticate(AuthenticationRequest $request): AuthenticationResult
            {
                return AuthenticationResult::failed(AuthenticationFailureReason::Rejected);
            }
        };

        $this->expectException(InvalidAuthenticatorException::class);
        $registry->register($authenticator);
    }

    public function testOrchestratorReturnsUnsupportedWithoutInvokingAnAuthenticator(): void
    {
        $result = (new AuthenticationOrchestrator(new AuthenticatorRegistry()))
            ->authenticate($this->request('unknown'));

        self::assertFalse($result->isSuccessful());
        self::assertSame(AuthenticationFailureReason::UnsupportedCredentials, $result->failure()?->reason());
    }

    public function testOrchestratorPreservesExpectedAuthenticationRejection(): void
    {
        $registry = new AuthenticatorRegistry();
        $registry->register($this->authenticator(
            'password-authenticator',
            ['password'],
            AuthenticationResult::failed(AuthenticationFailureReason::InvalidCredentials)
        ));

        $result = (new AuthenticationOrchestrator($registry))->authenticate($this->request('password'));

        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $result->failure()?->reason());
    }

    public function testOrchestratorSanitizesTechnicalFailureAndReportsItInternally(): void
    {
        $registry = new AuthenticatorRegistry();
        $registry->register($this->failingAuthenticator());
        $handler = new RecordingTechnicalFailureHandler();

        $result = (new AuthenticationOrchestrator($registry, $handler))->authenticate($this->request('password'));

        self::assertSame(AuthenticationFailureReason::InfrastructureFailure, $result->failure()?->reason());
        self::assertSame('password-authenticator', $handler->authenticatorId?->value());
        self::assertInstanceOf(RuntimeException::class, $handler->failure);
        self::assertSame('internal provider detail', $handler->failure->getMessage());

        $failure = $result->failure();
        self::assertNotNull($failure);
        self::assertSame(['reason' => 'infrastructure_failure'], $failure->toArray());
    }

    /** @param non-empty-list<string> $types */
    private function authenticator(
        string $id,
        array $types,
        ?AuthenticationResult $result = null
    ): AuthenticatorInterface {
        return new class ($id, $types, $result) implements AuthenticatorInterface {
            /** @param non-empty-list<string> $types */
            public function __construct(
                private readonly string $authenticatorId,
                private readonly array $types,
                private readonly ?AuthenticationResult $result
            ) {
            }

            public function id(): AuthenticatorId
            {
                return new AuthenticatorId($this->authenticatorId);
            }

            public function supportedCredentialTypes(): array
            {
                return array_map(
                    static fn (string $type): CredentialType => new CredentialType($type),
                    $this->types
                );
            }

            public function authenticate(AuthenticationRequest $request): AuthenticationResult
            {
                return $this->result ?? AuthenticationResult::failed(AuthenticationFailureReason::Rejected);
            }
        };
    }

    private function failingAuthenticator(): AuthenticatorInterface
    {
        return new class implements AuthenticatorInterface {
            public function id(): AuthenticatorId
            {
                return new AuthenticatorId('password-authenticator');
            }

            public function supportedCredentialTypes(): array
            {
                return [new CredentialType('password')];
            }

            public function authenticate(AuthenticationRequest $request): AuthenticationResult
            {
                throw new RuntimeException('internal provider detail');
            }
        };
    }

    private function request(string $type): AuthenticationRequest
    {
        $credential = new class ($type) implements CredentialInterface {
            public function __construct(private readonly string $type)
            {
            }

            public function type(): CredentialType
            {
                return new CredentialType($this->type);
            }
        };

        return new AuthenticationRequest(
            new AuthenticationRequestId('request-1'),
            $credential,
            new DateTimeImmutable('2026-08-05T12:00:00+00:00')
        );
    }
}

final class RecordingTechnicalFailureHandler implements AuthenticationTechnicalFailureHandlerInterface
{
    public ?AuthenticatorId $authenticatorId = null;

    public ?Throwable $failure = null;

    public function handle(
        AuthenticationRequest $request,
        AuthenticatorId $authenticatorId,
        Throwable $failure
    ): void {
        $this->authenticatorId = $authenticatorId;
        $this->failure = $failure;
    }
}
