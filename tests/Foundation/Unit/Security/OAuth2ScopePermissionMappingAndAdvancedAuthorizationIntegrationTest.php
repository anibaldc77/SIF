<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthAccessTokenAuthorizationAttributes;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthAuthorizationContext;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthPermissionResolver;
use Sif\Foundation\Security\OAuth2\Authorization\ScopePermissionMap;
use Sif\Foundation\Security\OAuth2\ScopeSet;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final class OAuth2ScopePermissionMappingAndAdvancedAuthorizationIntegrationTest extends TestCase
{
    public function testScopesMapExplicitlyToPermissions(): void
    {
        $map = $this->mapping();

        $permissions = $map->resolve(
            new ScopeSet(['invoice.read', 'invoice.approve'])
        );

        self::assertSame(
            ['invoice.approve', 'invoice.read'],
            $permissions->values()
        );
    }

    public function testUnknownScopeDoesNotImplicitlyBecomePermission(): void
    {
        $permissions = $this->mapping()->resolve(
            new ScopeSet(['unknown.scope'])
        );

        self::assertSame([], $permissions->values());
    }

    public function testOAuthPermissionResolverRequiresPrincipalSubjectMatch(): void
    {
        $resolver = new OAuthPermissionResolver(
            $this->token(),
            $this->mapping()
        );

        self::assertSame(
            ['invoice.approve', 'invoice.read'],
            $resolver->resolve($this->principal('oauth-user'))->values()
        );

        self::assertSame(
            [],
            $resolver->resolve($this->principal('different-user'))->values()
        );
    }

    public function testOAuthClaimsBecomeNamespacedAuthorizationAttributes(): void
    {
        $attributes = (new OAuthAccessTokenAuthorizationAttributes())->from(
            $this->token()
        );

        self::assertSame(
            'oauth-user',
            $attributes->get('oauth.subject')
        );
        self::assertSame(
            2,
            $attributes->get('oauth.scope.count')
        );
        self::assertSame(
            7,
            $attributes->get('oauth.claim.tenant.id')
        );
        self::assertSame(
            'client-1',
            $attributes->get('oauth.claim.client_id')
        );
    }

    public function testAuthorizationContextExposesPermissionsAndSubjectAttributesWithoutDecision(): void
    {
        $context = new OAuthAuthorizationContext(
            $this->token(),
            $this->mapping(),
            new OAuthAccessTokenAuthorizationAttributes()
        );

        self::assertSame(
            ['invoice.approve', 'invoice.read'],
            $context->permissions()->values()
        );
        self::assertSame(
            7,
            $context->subjectAttributes()->get('oauth.claim.tenant.id')
        );

        $source = file_get_contents(
            dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth2/Authorization/'
            . 'OAuthAuthorizationContext.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('AuthorizationDecision', $source);
        self::assertStringNotContainsString('allow(', $source);
        self::assertStringNotContainsString('deny(', $source);
    }

    public function testMappingLayerDoesNotMutateAuthenticationLevel(): void
    {
        $principal = $this->principal('oauth-user');

        $resolver = new OAuthPermissionResolver(
            $this->token(),
            $this->mapping()
        );

        self::assertSame(
            ['invoice.approve', 'invoice.read'],
            $resolver->resolve($principal)->values()
        );
        self::assertSame(
            50,
            $principal->evidence()->level()->value()
        );
    }

    private function mapping(): ScopePermissionMap
    {
        return new ScopePermissionMap([
            'invoice.read' => new PermissionSet([
                new PermissionIdentifier('invoice.read'),
            ]),
            'invoice.approve' => new PermissionSet([
                new PermissionIdentifier('invoice.approve'),
            ]),
        ]);
    }

    private function token(): ValidatedAccessToken
    {
        return new ValidatedAccessToken(
            new IdentityId('oauth-user'),
            new ScopeSet([
                'invoice.read',
                'invoice.approve',
            ]),
            new DateTimeImmutable('2026-08-07T22:00:00+00:00'),
            new DateTimeImmutable('2026-08-07T21:00:00+00:00'),
            [
                'tenant.id' => 7,
                'client_id' => 'client-1',
            ]
        );
    }

    private function principal(
        string $id
    ): AuthenticatedPrincipal {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId($id)),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('oauth2-access-token'),
                new AuthenticationLevel(50),
                new DateTimeImmutable('2026-08-07T21:00:00+00:00')
            )
        );
    }
}
