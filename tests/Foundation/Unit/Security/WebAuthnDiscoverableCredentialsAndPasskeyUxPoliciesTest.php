<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnCredentialSelectionPolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnDiscoverableCredentialPolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnPasskeyUxPolicyInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnDiscoverableCredentialProfile;
use Sif\Foundation\Security\WebAuthn\WebAuthnPasskeyUxContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnPasskeyUxDecision;

final class WebAuthnDiscoverableCredentialsAndPasskeyUxPoliciesTest extends TestCase
{
    public function testDiscoverableCredentialProfileKeepsPreferencesExplicit(): void
    {
        $profile = new WebAuthnDiscoverableCredentialProfile(
            true,
            'required',
            ['internal', 'hybrid'],
            ['client-device']
        );

        self::assertTrue($profile->discoverableRequired());
        self::assertSame('required', $profile->userVerification());
        self::assertSame(
            ['internal', 'hybrid'],
            $profile->preferredTransports()
        );
        self::assertSame(['client-device'], $profile->hints());
    }

    public function testPasskeyUxContextKeepsMediationAndUsernamelessCapabilitiesExplicit(): void
    {
        $context = new WebAuthnPasskeyUxContext(
            true,
            true,
            ['credential-001'],
            ['internal']
        );

        self::assertTrue($context->conditionalMediationAvailable());
        self::assertTrue($context->usernamelessFlowAllowed());
        self::assertSame(
            ['credential-001'],
            $context->availableCredentialIds()
        );
        self::assertSame(
            ['internal'],
            $context->availableTransports()
        );
    }

    public function testPasskeyUxDecisionKeepsPreferredCredentialsExplicit(): void
    {
        $decision = new WebAuthnPasskeyUxDecision(
            true,
            true,
            ['credential-002', 'credential-001'],
            ['cross-device transport may be required']
        );

        self::assertTrue($decision->allowConditionalMediation());
        self::assertTrue($decision->allowUsernamelessFlow());
        self::assertSame(
            ['credential-002', 'credential-001'],
            $decision->preferredCredentialIds()
        );
        self::assertSame(
            ['cross-device transport may be required'],
            $decision->warnings()
        );
    }

    public function testPasskeyUxContractsAreTyped(): void
    {
        $decision = new \ReflectionMethod(
            WebAuthnPasskeyUxPolicyInterface::class,
            'decide'
        );
        $selection = new \ReflectionMethod(
            WebAuthnCredentialSelectionPolicyInterface::class,
            'select'
        );

        self::assertSame(
            WebAuthnPasskeyUxDecision::class,
            (string) $decision->getReturnType()
        );
        self::assertSame(
            'array',
            (string) $selection->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                WebAuthnDiscoverableCredentialPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testPasskeyUxLayerRemainsBrowserAndUiNeutral(): void
    {
        foreach ([
            WebAuthnDiscoverableCredentialPolicyInterface::class,
            WebAuthnPasskeyUxPolicyInterface::class,
            WebAuthnCredentialSelectionPolicyInterface::class,
            WebAuthnDiscoverableCredentialProfile::class,
            WebAuthnPasskeyUxContext::class,
            WebAuthnPasskeyUxDecision::class,
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
            self::assertStringNotContainsString('DOM', $source);
            self::assertStringNotContainsString('HTML', $source);
            self::assertStringNotContainsString('CSS', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }
}
