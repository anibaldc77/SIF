<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkResolverInterface;
use Sif\Foundation\Security\Contracts\FederatedIdentityProvisionerInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionEstablisherInterface;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\OidcIdTokenParserInterface;
use Sif\Foundation\Security\Contracts\OidcTokenExchangerInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\Federation\FederatedAccountResolver;
use Sif\Foundation\Security\Oidc\Federation\FederatedAuthenticationMapper;
use Sif\Foundation\Security\Oidc\Federation\FederatedLoginOrchestrator;
use Sif\Foundation\Security\Oidc\Federation\FederatedPrincipalFactory;
use Sif\Foundation\Security\Oidc\Federation\FederatedProvisioningPolicy;
use Sif\Foundation\Security\Oidc\Federation\FederatedSecurityEvent;
use Sif\Foundation\Security\Oidc\Federation\LinkedLocalIdentity;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallback;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallbackValidator;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCode;
use Sif\Foundation\Security\Oidc\OidcAuthorizationRequest;
use Sif\Foundation\Security\Oidc\OidcAuthorizationTransaction;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Oidc\OidcIdToken;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidationPolicy;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidator;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcState;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequest;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequestFactory;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeResult;
use Sif\Foundation\Security\Oidc\PkceCodeChallenge;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;
use Sif\Foundation\Security\Oidc\PkceMethod;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

final class OpenIdConnectFederatedLoginOrchestrationSessionAndSecurityEventsTest extends TestCase
{
    public function testSuccessfulFederatedLoginEstablishesSessionAndPublishesSuccessEvent(): void
    {
        $session = new OidcFederatedSessionRecorder();
        $events = new OidcFederatedSecurityEventRecorder();

        $result = $this->orchestrator(
            $session,
            $events,
            true,
            true
        )->complete(
            $this->transaction(),
            $this->authorizationCallback(),
            $this->registration(),
            $this->now()
        );

        self::assertNotNull($result);
        self::assertSame(
            'local-user-1',
            $result->principal()->identity()->id()->value()
        );
        self::assertSame(
            ['local-user-1'],
            $session->identityIds()
        );
        self::assertSame(
            ['oidc.login.succeeded'],
            $events->names()
        );
    }

    public function testRejectedIdTokenDoesNotCreateSession(): void
    {
        $session = new OidcFederatedSessionRecorder();
        $events = new OidcFederatedSecurityEventRecorder();

        $result = $this->orchestrator(
            $session,
            $events,
            false,
            true
        )->complete(
            $this->transaction(),
            $this->authorizationCallback(),
            $this->registration(),
            $this->now()
        );

        self::assertNull($result);
        self::assertSame([], $session->identityIds());
        self::assertSame(
            ['oidc.login.id_token_rejected'],
            $events->names()
        );
    }

    public function testUnresolvedAccountDoesNotCreateSession(): void
    {
        $session = new OidcFederatedSessionRecorder();
        $events = new OidcFederatedSecurityEventRecorder();

        $result = $this->orchestrator(
            $session,
            $events,
            true,
            false
        )->complete(
            $this->transaction(),
            $this->authorizationCallback(),
            $this->registration(),
            $this->now()
        );

        self::assertNull($result);
        self::assertSame([], $session->identityIds());
        self::assertSame(
            ['oidc.login.account_unresolved'],
            $events->names()
        );
    }

    public function testSessionIsEstablishedOnlyAfterIdTokenAndAccountMappingSucceed(): void
    {
        $session = new OidcFederatedSessionRecorder();
        $events = new OidcFederatedSecurityEventRecorder();

        $this->orchestrator(
            $session,
            $events,
            true,
            true
        )->complete(
            $this->transaction(),
            $this->authorizationCallback(),
            $this->registration(),
            $this->now()
        );

        self::assertSame(1, $session->establishCalls());
        self::assertSame(1, count($events->names()));
    }

    public function testOrchestratorDoesNotCreateCookiesOrAuthorizationDecisions(): void
    {
        $reflection = new \ReflectionClass(
            FederatedLoginOrchestrator::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString(
            'setcookie(',
            strtolower($source)
        );
        self::assertStringNotContainsString(
            'AuthorizationDecision',
            $source
        );
        self::assertStringNotContainsString(
            'Response',
            $source
        );
    }

    public function testSecurityEventsDoNotContainTokenOrSecretMaterial(): void
    {
        $session = new OidcFederatedSessionRecorder();
        $events = new OidcFederatedSecurityEventRecorder();

        $this->orchestrator(
            $session,
            $events,
            true,
            true
        )->complete(
            $this->transaction(),
            $this->authorizationCallback(),
            $this->registration(),
            $this->now()
        );

        foreach ($events->events() as $event) {
            $serialized = json_encode($event->context());

            self::assertIsString($serialized);
            self::assertStringNotContainsString(
                'authorization-code-material',
                $serialized
            );
            self::assertStringNotContainsString(
                'header.payload.signature',
                $serialized
            );
            self::assertStringNotContainsString(
                'client-secret',
                $serialized
            );
        }
    }

    private function orchestrator(
        FederatedSessionEstablisherInterface $session,
        FederatedSecurityEventPublisherInterface $events,
        bool $validIdToken,
        bool $linkedAccount
    ): FederatedLoginOrchestrator {
        $exchanger = new class implements OidcTokenExchangerInterface {
            public function exchange(
                OidcTokenExchangeRequest $request
            ): OidcTokenExchangeResult {
                return new OidcTokenExchangeResult(
                    new OidcIdToken(
                        'header.payload.signature-material'
                    )
                );
            }
        };

        $parser = new class($this->now()) implements OidcIdTokenParserInterface {
            public function __construct(
                private DateTimeImmutable $now
            ) {
            }

            public function parse(
                OidcIdToken $idToken
            ): ParsedJwt {
                return new ParsedJwt(
                    new JwtHeader('RS256', 'key-1', 'JWT'),
                    new JwtClaims(
                        'subject-123',
                        'https://identity.example',
                        ['sif-client'],
                        $this->now->modify('+1 hour'),
                        $this->now,
                        null,
                        null,
                        [
                            'nonce' => 'abcdefghijklmnopqrstuvwxyz0123456789_NONCE',
                        ]
                    ),
                    'header.payload',
                    'signature'
                );
            }
        };

        $signature = new class($validIdToken) implements JwtSignatureVerifierInterface {
            public function __construct(
                private bool $valid
            ) {
            }

            public function verify(
                ParsedJwt $jwt
            ): bool {
                return $this->valid;
            }
        };

        $idTokenValidator = new OidcIdTokenValidator(
            $parser,
            $signature,
            new OidcIdTokenValidationPolicy(
                'https://identity.example',
                'sif-client',
                ['RS256'],
                new DateInterval('PT30S')
            )
        );

        $linkResolver = new class($linkedAccount) implements FederatedIdentityLinkResolverInterface {
            public function __construct(
                private bool $linked
            ) {
            }

            public function resolve(
                OidcFederatedIdentity $federatedIdentity
            ): ?LinkedLocalIdentity {
                if (!$this->linked) {
                    return null;
                }

                return new LinkedLocalIdentity(
                    new IdentityId('local-user-1'),
                    'provider-link-1'
                );
            }
        };

        $provisioner = new class implements FederatedIdentityProvisionerInterface {
            public function provision(
                OidcFederatedIdentity $federatedIdentity
            ): LinkedLocalIdentity {
                return new LinkedLocalIdentity(
                    new IdentityId('provisioned-user'),
                    'provisioned-link'
                );
            }
        };

        return new FederatedLoginOrchestrator(
            new OidcAuthorizationCallbackValidator(),
            new OidcTokenExchangeRequestFactory(),
            $exchanger,
            $idTokenValidator,
            new FederatedAuthenticationMapper(
                new FederatedAccountResolver(
                    $linkResolver,
                    $provisioner,
                    new FederatedProvisioningPolicy(false)
                ),
                new FederatedPrincipalFactory()
            ),
            $session,
            $events
        );
    }

    private function transaction(): OidcAuthorizationTransaction
    {
        $state = new OidcState(
            'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
        );
        $nonce = new OidcNonce(
            'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
        );
        $verifier = new PkceCodeVerifier(
            'abcdefghijklmnopqrstuvwxyz0123456789-._~VERIFIER'
        );

        return new OidcAuthorizationTransaction(
            $state,
            $nonce,
            $verifier,
            new OidcAuthorizationRequest(
                'https://identity.example/authorize',
                'sif-client',
                'https://app.example/oidc/callback',
                $state,
                $nonce,
                new PkceCodeChallenge(
                    'abcdefghijklmnopqrstuvwxyz0123456789_CHALLENGE'
                ),
                PkceMethod::S256,
                ['openid']
            )
        );
    }

    private function authorizationCallback(): OidcAuthorizationCallback
    {
        return new OidcAuthorizationCallback(
            new OidcState(
                'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
            ),
            new OidcAuthorizationCode(
                'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
            )
        );
    }

    private function registration(): OidcClientRegistration
    {
        return new OidcClientRegistration(
            'sif-client',
            'https://app.example/oidc/callback'
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T21:00:00+00:00'
        );
    }
}

final class OidcFederatedSessionRecorder implements FederatedSessionEstablisherInterface
{
    /** @var list<string> */
    private array $identityIds = [];

    private int $calls = 0;

    public function establish(
        AuthenticatedPrincipal $principal
    ): void {
        $this->calls++;
        $this->identityIds[] = $principal
            ->identity()
            ->id()
            ->value();
    }

    /** @return list<string> */
    public function identityIds(): array
    {
        return $this->identityIds;
    }

    public function establishCalls(): int
    {
        return $this->calls;
    }
}

final class OidcFederatedSecurityEventRecorder implements FederatedSecurityEventPublisherInterface
{
    /** @var list<FederatedSecurityEvent> */
    private array $events = [];

    public function publish(
        FederatedSecurityEvent $event
    ): void {
        $this->events[] = $event;
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_map(
            static fn (FederatedSecurityEvent $event): string => $event->name(),
            $this->events
        );
    }

    /** @return list<FederatedSecurityEvent> */
    public function events(): array
    {
        return $this->events;
    }
}
