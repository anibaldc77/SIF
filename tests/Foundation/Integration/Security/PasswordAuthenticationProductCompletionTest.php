<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\Uri;
use Sif\Foundation\Security\Authentication\AuthenticationOrchestrator;
use Sif\Foundation\Security\Authentication\AuthenticatorRegistry;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Http\Password\PasswordLoginEndpoint;
use Sif\Foundation\Security\Http\Password\PasswordLogoutEndpoint;
use Sif\Foundation\Security\Http\Password\PasswordSessionLoginService;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderRecord;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;
use Sif\Foundation\Security\Password\Authentication\PasswordAuthenticator;
use Sif\Foundation\Security\Password\Authentication\PasswordHashProviderResult;
use Sif\Foundation\Security\Password\Native\NativePasswordHasher;
use Sif\Foundation\Security\Password\Native\NativePasswordVerifier;
use Sif\Foundation\Security\Password\Native\PasswordHashPolicy;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\Rehash\PasswordRehashCoordinator;
use Sif\Foundation\Security\Password\StoredPasswordHash;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class PasswordAuthenticationProductCompletionTest extends TestCase
{
    public function testPasswordLoginEstablishesSessionAndUpgradesStoredHash(): void
    {
        [$login, , $session, $context, $store] = $this->fixture();

        $response = $login->handle(
            $this->request('{"identity":"alice","password":"correct-password"}'),
            $session,
            new DateTimeImmutable('2026-08-06T16:00:00Z')
        );

        self::assertSame(200, $response->status()->code());
        self::assertTrue($context->principal()->isAuthenticated());
        self::assertTrue($session->regenerationRequested());
        self::assertSame('no-store', $response->headers()->line('Cache-Control'));
        self::assertSame(5, $store->hash()->parameters()['cost'] ?? null);
    }

    public function testInvalidPasswordFailsClosedWithoutLeakingOrMutatingSession(): void
    {
        [$login, , $session, $context] = $this->fixture();

        $response = $login->handle(
            $this->request('{"identity":"alice","password":"incorrect-password"}'),
            $session,
            new DateTimeImmutable('2026-08-06T16:01:00Z')
        );

        self::assertSame(401, $response->status()->code());
        self::assertFalse($context->principal()->isAuthenticated());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertStringNotContainsString('incorrect-password', $response->body()->contents());
        self::assertSame('Password realm="application"', $response->headers()->line('WWW-Authenticate'));
    }

    public function testLogoutRemovesAuthenticatedPrincipalAndRequestsRegeneration(): void
    {
        [$login, $logout, $session, $context] = $this->fixture();
        $login->handle(
            $this->request('{"identity":"alice","password":"correct-password"}'),
            $session,
            new DateTimeImmutable('2026-08-06T16:02:00Z')
        );

        $response = $logout->handle($session);

        self::assertSame(200, $response->status()->code());
        self::assertFalse($context->principal()->isAuthenticated());
        self::assertFalse($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());
    }

    /** @return array{PasswordLoginEndpoint, PasswordLogoutEndpoint, SessionState, SecurityContext, InMemoryPasswordHashStore} */
    private function fixture(): array
    {
        $identity = new Identity(new IdentityId('user-228'));
        $identityProvider = new ProductIdentityProvider($identity);
        $legacyHasher = new NativePasswordHasher(PasswordHashPolicy::bcrypt(4));
        $targetPolicy = PasswordHashPolicy::bcrypt(5);
        $store = new InMemoryPasswordHashStore(
            $legacyHasher->hash(new PasswordSecret('correct-password'))
        );

        $authenticator = new PasswordAuthenticator(
            $identityProvider,
            $store,
            new NativePasswordVerifier($targetPolicy),
            $legacyHasher->hash(new PasswordSecret('fallback-password')),
            rehashCoordinator: new PasswordRehashCoordinator(
                new NativePasswordHasher($targetPolicy),
                $store
            )
        );

        $registry = new AuthenticatorRegistry();
        $registry->register($authenticator);
        $context = new SecurityContext();
        $service = new PasswordSessionLoginService(
            new AuthenticationOrchestrator($registry),
            new SessionAuthenticationManager(),
            $context
        );
        $now = new DateTimeImmutable('2026-08-06T16:00:00Z');
        $session = new SessionState(new SessionId(str_repeat('b', 32)), [], $now, $now);

        return [
            new PasswordLoginEndpoint($service),
            new PasswordLogoutEndpoint($service),
            $session,
            $context,
            $store,
        ];
    }

    private function request(string $json): RequestInterface
    {
        return new Request(
            HttpMethod::Post,
            Uri::fromString('/login'),
            body: new RequestBody($json, 'application/json', 'utf-8')
        );
    }
}

final readonly class ProductIdentityProvider implements IdentityProviderInterface
{
    public function __construct(private IdentityInterface $identity)
    {
    }

    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('product-provider');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        if ($lookupKey->value() !== 'alice') {
            return IdentityProviderResult::notFound();
        }

        return IdentityProviderResult::found(
            new IdentityProviderRecord($this->identity, IdentityAccountStatus::Active)
        );
    }
}

final class InMemoryPasswordHashStore implements PasswordHashStoreInterface
{
    public function __construct(private StoredPasswordHash $hash)
    {
    }

    public function findFor(IdentityInterface $identity): PasswordHashProviderResult
    {
        return PasswordHashProviderResult::found($this->hash);
    }

    public function replaceFor(IdentityInterface $identity, StoredPasswordHash $hash): void
    {
        $this->hash = $hash;
    }

    public function hash(): StoredPasswordHash
    {
        return $this->hash;
    }
}
