<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnAuthenticationVerifierInterface;
use Sif\Foundation\Security\Contracts\WebAuthnAuthenticatorPolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnChallengeStoreInterface;
use Sif\Foundation\Security\Contracts\WebAuthnCredentialRepositoryInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRegistrationVerifierInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;
use Sif\Foundation\Security\WebAuthn\WebAuthnRegistrationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnVerificationResult;

final class WebAuthnFido2AndPasskeyAuthenticationArchitectureTest extends TestCase
{
    public function testCredentialKeepsRpUserPublicKeyAndCounterExplicit(): void
    {
        $credential = new WebAuthnCredential(
            'credential-001',
            'user-001',
            'example.test',
            'public-key-material',
            7,
            ['internal']
        );

        self::assertSame('credential-001', $credential->credentialId());
        self::assertSame('user-001', $credential->userHandle());
        self::assertSame('example.test', $credential->relyingPartyId());
        self::assertSame('public-key-material', $credential->publicKey());
        self::assertSame(7, $credential->signatureCounter());
        self::assertSame(['internal'], $credential->transports());
    }

    public function testRegistrationContextKeepsRpOriginUserAndChallengeExplicit(): void
    {
        $context = new WebAuthnRegistrationContext(
            'example.test',
            'https://example.test',
            'user-001',
            'alice',
            'challenge-001'
        );

        self::assertSame('example.test', $context->relyingPartyId());
        self::assertSame('https://example.test', $context->origin());
        self::assertSame('user-001', $context->userId());
        self::assertSame('alice', $context->userName());
        self::assertSame('challenge-001', $context->challenge());
    }

    public function testAuthenticationContextSupportsDiscoverableCredentials(): void
    {
        $context = new WebAuthnAuthenticationContext(
            'example.test',
            'https://example.test',
            'challenge-002'
        );

        self::assertNull($context->userHandle());
    }

    public function testVerificationResultAggregatesSecurityChecks(): void
    {
        $result = new WebAuthnVerificationResult(
            true,
            true,
            true,
            true,
            true
        );

        self::assertTrue($result->valid());
        self::assertTrue($result->challengeValid());
        self::assertTrue($result->originValid());
        self::assertTrue($result->relyingPartyValid());
        self::assertTrue($result->userVerificationSatisfied());
    }

    public function testVerifierContractsAreTyped(): void
    {
        $registration = new \ReflectionMethod(
            WebAuthnRegistrationVerifierInterface::class,
            'verify'
        );
        $authentication = new \ReflectionMethod(
            WebAuthnAuthenticationVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            WebAuthnCredential::class,
            (string) $registration->getReturnType()
        );
        self::assertSame(
            WebAuthnVerificationResult::class,
            (string) $authentication->getReturnType()
        );

        foreach ([
            WebAuthnCredentialRepositoryInterface::class,
            WebAuthnChallengeStoreInterface::class,
            WebAuthnAuthenticatorPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testArchitectureRemainsBrowserCryptoAndStorageNeutral(): void
    {
        foreach ([
            WebAuthnRegistrationVerifierInterface::class,
            WebAuthnAuthenticationVerifierInterface::class,
            WebAuthnCredentialRepositoryInterface::class,
            WebAuthnChallengeStoreInterface::class,
            WebAuthnAuthenticatorPolicyInterface::class,
            WebAuthnCredential::class,
            WebAuthnRegistrationContext::class,
            WebAuthnAuthenticationContext::class,
            WebAuthnVerificationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('navigator.credentials', strtolower($source));
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
