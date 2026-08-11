<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnAssertionValidatorInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRequestOptionsFactoryInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRequestOptionsSerializerInterface;
use Sif\Foundation\Security\Contracts\WebAuthnSignatureCounterPolicyInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnAssertion;
use Sif\Foundation\Security\WebAuthn\WebAuthnAssertionValidationResult;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialDescriptor;
use Sif\Foundation\Security\WebAuthn\WebAuthnRequestOptions;

final class WebAuthnAuthenticationCeremonyAndAssertionValidationTest extends TestCase
{
    public function testRequestOptionsModelAuthenticationCeremonyInputs(): void
    {
        $options = new WebAuthnRequestOptions(
            'challenge-003',
            'example.test',
            45000,
            [
                new WebAuthnCredentialDescriptor(
                    'credential-001',
                    ['internal']
                ),
            ],
            'required',
            ['security-key']
        );

        self::assertSame('challenge-003', $options->challenge());
        self::assertSame('example.test', $options->relyingPartyId());
        self::assertSame(45000, $options->timeoutMilliseconds());
        self::assertCount(1, $options->allowCredentials());
        self::assertSame('required', $options->userVerification());
        self::assertSame(['security-key'], $options->hints());
    }

    public function testAssertionKeepsCredentialAndSignedInputsExplicit(): void
    {
        $assertion = new WebAuthnAssertion(
            'credential-001',
            'client-data-json',
            'authenticator-data',
            'signature',
            'user-001'
        );

        self::assertSame('credential-001', $assertion->credentialId());
        self::assertSame('client-data-json', $assertion->clientDataJson());
        self::assertSame('authenticator-data', $assertion->authenticatorData());
        self::assertSame('signature', $assertion->signature());
        self::assertSame('user-001', $assertion->userHandle());
    }

    public function testValidationResultAggregatesSecurityChecks(): void
    {
        $result = new WebAuthnAssertionValidationResult(
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            8
        );

        self::assertTrue($result->valid());
        self::assertTrue($result->signatureValid());
        self::assertTrue($result->counterValid());
        self::assertSame(8, $result->newSignatureCounter());
    }

    public function testCounterViolationInvalidatesOverallResult(): void
    {
        $result = new WebAuthnAssertionValidationResult(
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            null,
            ['signature_counter_regression']
        );

        self::assertFalse($result->valid());
        self::assertSame(
            ['signature_counter_regression'],
            $result->violations()
        );
    }

    public function testAuthenticationContractsAreTyped(): void
    {
        $factory = new \ReflectionMethod(
            WebAuthnRequestOptionsFactoryInterface::class,
            'create'
        );
        $serializer = new \ReflectionMethod(
            WebAuthnRequestOptionsSerializerInterface::class,
            'serialize'
        );
        $validator = new \ReflectionMethod(
            WebAuthnAssertionValidatorInterface::class,
            'validate'
        );

        self::assertSame(
            WebAuthnRequestOptions::class,
            (string) $factory->getReturnType()
        );
        self::assertSame(
            'string',
            (string) $serializer->getReturnType()
        );
        self::assertSame(
            WebAuthnAssertionValidationResult::class,
            (string) $validator->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                WebAuthnSignatureCounterPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testAuthenticationLayerRemainsBrowserCryptoAndStorageNeutral(): void
    {
        foreach ([
            WebAuthnRequestOptionsFactoryInterface::class,
            WebAuthnRequestOptionsSerializerInterface::class,
            WebAuthnAssertionValidatorInterface::class,
            WebAuthnSignatureCounterPolicyInterface::class,
            WebAuthnRequestOptions::class,
            WebAuthnAssertion::class,
            WebAuthnAssertionValidationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'navigator.credentials',
                strtolower($source)
            );
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
