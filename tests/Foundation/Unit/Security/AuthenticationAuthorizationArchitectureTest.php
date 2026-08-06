<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationState;
use Sif\Foundation\Security\Contracts\PrincipalInterface;
use Sif\Foundation\Security\Identity\AnonymousPrincipal;

final class AuthenticationAuthorizationArchitectureTest extends TestCase
{
    public function testAnonymousPrincipalExposesExplicitUnauthenticatedState(): void
    {
        $principal = new AnonymousPrincipal();

        self::assertInstanceOf(PrincipalInterface::class, $principal);
        self::assertSame(AuthenticationState::Anonymous, $principal->authenticationState());
        self::assertFalse($principal->isAuthenticated());
    }

    public function testAuthenticationStateUsesStableTransportNeutralValues(): void
    {
        self::assertSame('anonymous', AuthenticationState::Anonymous->value);
        self::assertSame('authenticated', AuthenticationState::Authenticated->value);
    }

    public function testSecurityFoundationDoesNotTreatSessionIdentifierAsPrincipalIdentity(): void
    {
        $securityRoot = dirname(__DIR__, 4) . '/src/Foundation/Security';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($securityRoot));

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringNotContainsString('Sif\\Foundation\\Session\\SessionId', $contents);
        }
    }
}
