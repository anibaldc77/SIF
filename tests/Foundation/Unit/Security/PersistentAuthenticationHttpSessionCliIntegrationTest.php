<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Security\PersistentAuthenticationInspectCommand;
use Sif\Foundation\Cli\Command\Security\PersistentAuthenticationRevokeCommand;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationPrincipalFactoryInterface;
use Sif\Foundation\Security\Http\PersistentAuthentication\PersistentAuthenticationCookiePayload;
use Sif\Foundation\Security\Http\PersistentAuthentication\PersistentAuthenticationHttpRestorationService;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\PersistentAuthentication\InMemoryPersistentAuthenticationCredentialStore;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationService;
use Sif\Foundation\Security\PersistentAuthentication\SecurePersistentAuthenticationTokenGenerator;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class PersistentAuthenticationHttpSessionCliIntegrationTest extends TestCase
{
    public function testCookiePayloadIsRedactedAndRoundTrips(): void
    {
        $token = (new SecurePersistentAuthenticationTokenGenerator())->generate();
        $payload = PersistentAuthenticationCookiePayload::fromToken($token);

        $raw = $payload->expose(static fn (string $value): string => $value);
        $parsed = PersistentAuthenticationCookiePayload::parse($raw);

        self::assertTrue($parsed->selector()->equals($token->selector()));
        self::assertTrue(
            $parsed->validatorDigest()->equals($token->validatorDigest())
        );
        self::assertSame('[REDACTED]', (string) $payload);
    }

    public function testHttpRestorationReturnsRotatedCookieMaterial(): void
    {
        [$http, $issuer] = $this->fixture();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('http-persistent-user'),
            $now,
            $now->modify('+30 days')
        );

        $cookie = PersistentAuthenticationCookiePayload::fromToken($token);
        $raw = $cookie->expose(static fn (string $value): string => $value);

        $result = $http->restoreFromCookie(
            $raw,
            $this->session($now),
            new SecurityContext(),
            $now->modify('+1 hour')
        );

        self::assertTrue($result->isRestored());

        $replacement = $http->replacementCookie($result);
        self::assertNotNull($replacement);

        $replacementRaw = $replacement->expose(
            static fn (string $value): string => $value
        );

        self::assertNotSame($raw, $replacementRaw);
    }

    public function testCliInspectExposesOnlySanitizedSnapshot(): void
    {
        $store = new InMemoryPersistentAuthenticationCredentialStore();
        $issuer = new PersistentAuthenticationService(
            $store,
            new SecurePersistentAuthenticationTokenGenerator()
        );
        $token = $issuer->issue(
            new IdentityId('cli-user'),
            $this->now(),
            $this->now()->modify('+30 days')
        );

        $result = (new PersistentAuthenticationInspectCommand($store))->execute(
            new CliInvocation(
                new CliCommandName('security:persistent:inspect'),
                [$token->selector()->value()]
            )
        );

        self::assertTrue($result->exitCode()->successful());
        $encoded = json_encode($result->data(), JSON_THROW_ON_ERROR);
        self::assertStringContainsString('selector_fingerprint', $encoded);
        self::assertStringNotContainsString('cli-user', $encoded);
        self::assertStringNotContainsString(
            $token->validatorDigest()->value(),
            $encoded
        );
    }

    public function testCliRevokePreventsSubsequentRestoration(): void
    {
        [$http, $issuer, $store] = $this->fixture();
        $now = $this->now();
        $token = $issuer->issue(
            new IdentityId('cli-revoke-user'),
            $now,
            $now->modify('+30 days')
        );

        $result = (new PersistentAuthenticationRevokeCommand($store))->execute(
            new CliInvocation(
                new CliCommandName('security:persistent:revoke'),
                [$token->selector()->value()]
            )
        );

        self::assertTrue($result->exitCode()->successful());

        $cookie = PersistentAuthenticationCookiePayload::fromToken($token);
        $raw = $cookie->expose(static fn (string $value): string => $value);

        self::assertFalse(
            $http->restoreFromCookie(
                $raw,
                $this->session($now),
                new SecurityContext(),
                $now->modify('+1 hour')
            )->isRestored()
        );
    }

    /** @return array{PersistentAuthenticationHttpRestorationService,PersistentAuthenticationService,InMemoryPersistentAuthenticationCredentialStore} */
    private function fixture(): array
    {
        $store = new InMemoryPersistentAuthenticationCredentialStore();
        $issuer = new PersistentAuthenticationService(
            $store,
            new SecurePersistentAuthenticationTokenGenerator()
        );

        $factory = new class implements PersistentAuthenticationPrincipalFactoryInterface {
            public function create(
                IdentityId $identityId,
                AuthenticationEvidence $evidence
            ): ?AuthenticatedPrincipal {
                return new AuthenticatedPrincipal(
                    new Identity($identityId),
                    new PrincipalAttributeCollection(),
                    $evidence
                );
            }
        };

        $restoration = new PersistentSessionRestorationService(
            $store,
            $issuer,
            $factory,
            new SessionAuthenticationManager(),
            new AuthenticationLevel(20)
        );

        return [
            new PersistentAuthenticationHttpRestorationService($restoration),
            $issuer,
            $store,
        ];
    }

    private function session(DateTimeImmutable $now): SessionState
    {
        return new SessionState(
            new SessionId(str_repeat('e', 32)),
            [],
            $now,
            $now
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T16:00:00+00:00');
    }
}
