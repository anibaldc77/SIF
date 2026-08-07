<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationCookieValue;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationToken;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationValidator;
use Sif\Foundation\Security\PersistentAuthentication\SecurePersistentAuthenticationTokenGenerator;

final class PersistentAuthenticationTokenMaterialAndSecureGenerationTest extends TestCase
{
    public function testSecureGeneratorProducesDistinctSelectorAndValidatorMaterial(): void
    {
        $generator = new SecurePersistentAuthenticationTokenGenerator();

        $first = $generator->generate();
        $second = $generator->generate();

        self::assertNotSame(
            $first->selector()->value(),
            $second->selector()->value()
        );
        self::assertFalse(
            $first->validatorDigest()->equals($second->validatorDigest())
        );
        self::assertSame(32, strlen($first->selector()->value()));
        self::assertSame(64, strlen($first->validatorDigest()->value()));
    }

    public function testValidatorAndTokenAreRedactedAndNotSerializable(): void
    {
        $token = new PersistentAuthenticationToken(
            new PersistentAuthenticationSelector('selector-abcdef0123456789'),
            new PersistentAuthenticationValidator(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789_-'
            )
        );

        self::assertSame('[REDACTED]', (string) $token->validator());
        self::assertSame(
            '[REDACTED]',
            $token->__debugInfo()['validator']
        );

        $this->expectException(\LogicException::class);
        serialize($token);
    }

    public function testCookieValueRoundTripsWithoutChangingCanonicalMaterial(): void
    {
        $token = new PersistentAuthenticationToken(
            new PersistentAuthenticationSelector('selector-abcdef0123456789'),
            new PersistentAuthenticationValidator(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789_-'
            )
        );

        $cookie = new PersistentAuthenticationCookieValue($token);

        $raw = $cookie->expose(
            static fn (string $value): string => $value
        );

        $parsed = PersistentAuthenticationCookieValue::parse($raw);

        self::assertTrue(
            $parsed->selector()->equals($token->selector())
        );
        self::assertTrue(
            $parsed->validatorDigest()->equals($token->validatorDigest())
        );
        self::assertSame('[REDACTED]', (string) $cookie);
    }

    public function testCookieValueCannotBeSerialized(): void
    {
        $cookie = new PersistentAuthenticationCookieValue(
            (new SecurePersistentAuthenticationTokenGenerator())->generate()
        );

        $this->expectException(\LogicException::class);
        serialize($cookie);
    }

    public function testValidatorDigestIsDeterministicWithoutExposingValidator(): void
    {
        $validator = new PersistentAuthenticationValidator(
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789_-'
        );

        $first = $validator->digest();
        $second = $validator->digest();

        self::assertTrue($first->equals($second));
        self::assertSame(64, strlen($first->value()));
        self::assertStringNotContainsString(
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $first->value()
        );
    }

    public function testGeneratorRejectsInsufficientEntropyConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SecurePersistentAuthenticationTokenGenerator(
            selectorBytes: 4,
            validatorBytes: 8
        );
    }
}
