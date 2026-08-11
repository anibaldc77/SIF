<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnCreationOptionsFactoryInterface;
use Sif\Foundation\Security\Contracts\WebAuthnCreationOptionsSerializerInterface;
use Sif\Foundation\Security\Contracts\WebAuthnRegistrationPolicyInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticatorSelection;
use Sif\Foundation\Security\WebAuthn\WebAuthnCreationOptions;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredentialDescriptor;
use Sif\Foundation\Security\WebAuthn\WebAuthnRelyingParty;
use Sif\Foundation\Security\WebAuthn\WebAuthnUserEntity;

final class WebAuthnRegistrationCeremonyAndCreationOptionsTest extends TestCase
{
    public function testRelyingPartyAndUserEntitiesAreExplicit(): void
    {
        $rp = new WebAuthnRelyingParty('example.test', 'Example');
        $user = new WebAuthnUserEntity('user-001', 'alice', 'Alice Example');

        self::assertSame('example.test', $rp->id());
        self::assertSame('Example', $rp->name());
        self::assertSame('user-001', $user->id());
        self::assertSame('alice', $user->name());
        self::assertSame('Alice Example', $user->displayName());
    }

    public function testAuthenticatorSelectionKeepsPreferencesExplicit(): void
    {
        $selection = new WebAuthnAuthenticatorSelection(
            'platform',
            'required',
            'required'
        );

        self::assertSame('platform', $selection->authenticatorAttachment());
        self::assertSame('required', $selection->residentKey());
        self::assertSame('required', $selection->userVerification());
    }

    public function testCredentialDescriptorKeepsTransportsExplicit(): void
    {
        $descriptor = new WebAuthnCredentialDescriptor(
            'credential-001',
            ['internal', 'hybrid']
        );

        self::assertSame('credential-001', $descriptor->credentialId());
        self::assertSame(['internal', 'hybrid'], $descriptor->transports());
    }

    public function testCreationOptionsModelRegistrationCeremonyInputs(): void
    {
        $options = new WebAuthnCreationOptions(
            new WebAuthnRelyingParty('example.test', 'Example'),
            new WebAuthnUserEntity('user-001', 'alice', 'Alice Example'),
            'challenge-001',
            [-7, -257],
            60000,
            new WebAuthnAuthenticatorSelection('platform', 'preferred', 'required'),
            [new WebAuthnCredentialDescriptor('existing-credential-001', ['internal'])],
            'none',
            ['client-device']
        );

        self::assertSame('example.test', $options->relyingParty()->id());
        self::assertSame('user-001', $options->user()->id());
        self::assertSame('challenge-001', $options->challenge());
        self::assertSame([-7, -257], $options->publicKeyCredentialParameters());
        self::assertSame(60000, $options->timeoutMilliseconds());
        self::assertCount(1, $options->excludeCredentials());
        self::assertSame('none', $options->attestation());
        self::assertSame(['client-device'], $options->hints());
    }

    public function testCreationContractsAreTyped(): void
    {
        $factory = new \ReflectionMethod(
            WebAuthnCreationOptionsFactoryInterface::class,
            'create'
        );
        $serializer = new \ReflectionMethod(
            WebAuthnCreationOptionsSerializerInterface::class,
            'serialize'
        );

        self::assertSame(
            WebAuthnCreationOptions::class,
            (string) $factory->getReturnType()
        );
        self::assertSame(
            'string',
            (string) $serializer->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(WebAuthnRegistrationPolicyInterface::class))->isInterface()
        );
    }

    public function testRegistrationLayerRemainsBrowserAndTransportNeutral(): void
    {
        foreach ([
            WebAuthnCreationOptionsFactoryInterface::class,
            WebAuthnCreationOptionsSerializerInterface::class,
            WebAuthnRegistrationPolicyInterface::class,
            WebAuthnRelyingParty::class,
            WebAuthnUserEntity::class,
            WebAuthnAuthenticatorSelection::class,
            WebAuthnCredentialDescriptor::class,
            WebAuthnCreationOptions::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('navigator.credentials', strtolower($source));
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
