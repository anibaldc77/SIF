<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkResolverInterface;
use Sif\Foundation\Security\Contracts\FederatedIdentityProvisionerInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionEstablisherInterface;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\OidcIdTokenParserInterface;
use Sif\Foundation\Security\Contracts\OidcNonceGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OidcStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcTokenExchangerInterface;
use Sif\Foundation\Security\Contracts\PkceCodeVerifierGeneratorInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\Federation\FederatedAccountResolver;
use Sif\Foundation\Security\Oidc\Federation\FederatedAuthenticationMapper;
use Sif\Foundation\Security\Oidc\Federation\FederatedLoginOrchestrator;
use Sif\Foundation\Security\Oidc\Federation\FederatedPrincipalFactory;
use Sif\Foundation\Security\Oidc\Federation\FederatedProvisioningPolicy;
use Sif\Foundation\Security\Oidc\Federation\FederatedSecurityEvent;
use Sif\Foundation\Security\Oidc\Federation\LinkedLocalIdentity;
use Sif\Foundation\Security\Oidc\Http\OidcHttpLoginStartService;
use Sif\Foundation\Security\Oidc\Logout\OidcLogoutRequest;
use Sif\Foundation\Security\Oidc\Logout\StandardOidcLogoutRedirectProvider;
use Sif\Foundation\Security\Oidc\NativeS256PkceCodeChallengeFactory;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallback;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallbackValidator;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCode;
use Sif\Foundation\Security\Oidc\OidcAuthorizationRequestFactory;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Oidc\OidcIdToken;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidationPolicy;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidator;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcProviderMetadata;
use Sif\Foundation\Security\Oidc\OidcState;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequest;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequestFactory;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeResult;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

final class OpenIdConnectFederatedAuthenticationProductCompletionTest extends TestCase
{
    public function testEndToEndFederatedLoginProducesLocalPrincipalAndSession(): void
    {
        $session = new ProductFederatedSessionRecorder();
        $events = new ProductFederatedEventRecorder();

        $registration = new OidcClientRegistration(
            'sif-client',
            'https://app.example/oidc/callback'
        );

        $transaction = (new OidcHttpLoginStartService(
            $this->authorizationRequestFactory()
        ))->start(
            $registration,
            ['openid', 'profile', 'email']
        )->transaction();

        $result = $this->orchestrator(
            $session,
            $events,
            true,
            true
        )->complete(
            $transaction,
            new OidcAuthorizationCallback(
                $transaction->state(),
                new OidcAuthorizationCode(
                    'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
                )
            ),
            $registration,
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

    public function testInvalidIdTokenFailsClosedBeforeSession(): void
    {
        $session = new ProductFederatedSessionRecorder();
        $events = new ProductFederatedEventRecorder();
        $transaction = (new OidcHttpLoginStartService(
            $this->authorizationRequestFactory()
        ))->start(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            )
        )->transaction();

        $result = $this->orchestrator(
            $session,
            $events,
            false,
            true
        )->complete(
            $transaction,
            new OidcAuthorizationCallback(
                $transaction->state(),
                new OidcAuthorizationCode(
                    'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
                )
            ),
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            ),
            $this->now()
        );

        self::assertNull($result);
        self::assertSame([], $session->identityIds());
    }

    public function testUnknownFederatedAccountFailsClosedWithoutProvisioningPolicy(): void
    {
        $session = new ProductFederatedSessionRecorder();
        $events = new ProductFederatedEventRecorder();
        $transaction = (new OidcHttpLoginStartService(
            $this->authorizationRequestFactory()
        ))->start(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            )
        )->transaction();

        $result = $this->orchestrator(
            $session,
            $events,
            true,
            false
        )->complete(
            $transaction,
            new OidcAuthorizationCallback(
                $transaction->state(),
                new OidcAuthorizationCode(
                    'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
                )
            ),
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            ),
            $this->now()
        );

        self::assertNull($result);
        self::assertSame([], $session->identityIds());
    }

    public function testLogoutRemainsProviderNeutralAndHttpNeutral(): void
    {
        $redirect = (new StandardOidcLogoutRedirectProvider())->createRedirect(
            new OidcLogoutRequest(
                'https://identity.example/logout',
                new OidcIdToken(
                    'header.payload.signature-material'
                ),
                'https://app.example/logout-complete'
            )
        );

        self::assertSame(
            'https://identity.example/logout',
            $redirect->location()
        );
        self::assertArrayHasKey(
            'id_token_hint',
            $redirect->query()
        );
    }

    public function testProductContainsNoProviderSpecificCoreDependency(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        ) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Auth0', $source);
            self::assertStringNotContainsString('Okta', $source);
        }
    }

    public function testProductDoesNotImplementAuthorizationServerOrDuplicateSessionStorage(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        ) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('AuthorizationServer', $source);
            self::assertStringNotContainsString('session_start(', strtolower($source));
            self::assertStringNotContainsString('setcookie(', strtolower($source));
        }
    }

    private function authorizationRequestFactory(): OidcAuthorizationRequestFactory
    {
        $metadata = new class implements OidcProviderMetadataProviderInterface {
            public function get(): OidcProviderMetadata
            {
                return new OidcProviderMetadata(
                    'https://identity.example',
                    'https://identity.example/authorize',
                    'https://identity.example/token',
                    'https://identity.example/jwks',
                    ['code'],
                    ['public'],
                    ['RS256'],
                    'https://identity.example/userinfo'
                );
            }

            public function refresh(): OidcProviderMetadata
            {
                return $this->get();
            }
        };

        $state = new class implements OidcStateGeneratorInterface {
            public function generate(): OidcState
            {
                return new OidcState(
                    'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
                );
            }
        };

        $nonce = new class implements OidcNonceGeneratorInterface {
            public function generate(): OidcNonce
            {
                return new OidcNonce(
                    'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
                );
            }
        };

        $verifier = new class implements PkceCodeVerifierGeneratorInterface {
            public function generate(): PkceCodeVerifier
            {
                return new PkceCodeVerifier(
                    'abcdefghijklmnopqrstuvwxyz0123456789-._~VERIFIER'
                );
            }
        };

        return new OidcAuthorizationRequestFactory(
            $metadata,
            $state,
            $nonce,
            $verifier,
            new NativeS256PkceCodeChallengeFactory()
        );
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
                            'email' => 'user@example.test',
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

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T21:00:00+00:00'
        );
    }
}

final class ProductFederatedSessionRecorder implements FederatedSessionEstablisherInterface
{
    /** @var list<string> */
    private array $identityIds = [];

    public function establish(
        AuthenticatedPrincipal $principal
    ): void {
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
}

final class ProductFederatedEventRecorder implements FederatedSecurityEventPublisherInterface
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
}
