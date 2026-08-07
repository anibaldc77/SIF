<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkResolverInterface;
use Sif\Foundation\Security\Contracts\FederatedIdentityProvisionerInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\Federation\FederatedAccountResolver;
use Sif\Foundation\Security\Oidc\Federation\FederatedAuthenticationMapper;
use Sif\Foundation\Security\Oidc\Federation\FederatedPrincipalFactory;
use Sif\Foundation\Security\Oidc\Federation\FederatedProvisioningPolicy;
use Sif\Foundation\Security\Oidc\Federation\LinkedLocalIdentity;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final class OpenIdConnectFederatedAccountLinkingProvisioningAndPrincipalMappingTest extends TestCase
{
    public function testExistingFederatedLinkMapsToExistingLocalIdentity(): void
    {
        $mapper = $this->mapper(
            new LinkedLocalIdentity(
                new IdentityId('local-user-1'),
                'provider-link-1'
            ),
            false
        );

        $result = $mapper->map(
            $this->federatedIdentity(),
            new DateTimeImmutable('2026-08-07T21:00:00+00:00')
        );

        self::assertNotNull($result);
        self::assertSame(
            'local-user-1',
            $result->principal()->identity()->id()->value()
        );
        self::assertSame(
            'provider-link-1',
            $result->linkedIdentity()->providerKey()
        );
    }

    public function testUnknownFederatedIdentityDoesNotProvisionWhenPolicyDisallowsIt(): void
    {
        $provisionCalls = 0;

        $resolver = new FederatedAccountResolver(
            new class implements FederatedIdentityLinkResolverInterface {
                public function resolve(
                    OidcFederatedIdentity $federatedIdentity
                ): ?LinkedLocalIdentity {
                    return null;
                }
            },
            new class($provisionCalls) implements FederatedIdentityProvisionerInterface {
                public function __construct(
                    private int &$calls
                ) {
                }

                public function provision(
                    OidcFederatedIdentity $federatedIdentity
                ): LinkedLocalIdentity {
                    $this->calls++;

                    return new LinkedLocalIdentity(
                        new IdentityId('provisioned-user'),
                        'provisioned-link'
                    );
                }
            },
            new FederatedProvisioningPolicy(false)
        );

        self::assertNull(
            $resolver->resolve($this->federatedIdentity())
        );
        self::assertSame(0, $provisionCalls);
    }

    public function testAutomaticProvisioningRequiresExplicitPolicy(): void
    {
        $provisionCalls = 0;

        $resolver = new FederatedAccountResolver(
            new class implements FederatedIdentityLinkResolverInterface {
                public function resolve(
                    OidcFederatedIdentity $federatedIdentity
                ): ?LinkedLocalIdentity {
                    return null;
                }
            },
            new class($provisionCalls) implements FederatedIdentityProvisionerInterface {
                public function __construct(
                    private int &$calls
                ) {
                }

                public function provision(
                    OidcFederatedIdentity $federatedIdentity
                ): LinkedLocalIdentity {
                    $this->calls++;

                    return new LinkedLocalIdentity(
                        new IdentityId('provisioned-user'),
                        'provisioned-link'
                    );
                }
            },
            new FederatedProvisioningPolicy(true)
        );

        $linked = $resolver->resolve(
            $this->federatedIdentity()
        );

        self::assertNotNull($linked);
        self::assertSame(
            'provisioned-user',
            $linked->identityId()->value()
        );
        self::assertSame(1, $provisionCalls);
    }

    public function testPrincipalUsesLocalIdentityButRetainsFederationAttributes(): void
    {
        $result = $this->mapper(
            new LinkedLocalIdentity(
                new IdentityId('local-user-1'),
                'provider-link-1'
            ),
            false
        )->map(
            $this->federatedIdentity(),
            new DateTimeImmutable('2026-08-07T21:00:00+00:00')
        );

        self::assertNotNull($result);

        $principal = $result->principal();

        self::assertSame(
            'local-user-1',
            $principal->identity()->id()->value()
        );

        $issuer = $principal->attributes()->get(
            'federation.issuer'
        );
        self::assertNotNull($issuer);
        self::assertSame(
            'https://identity.example',
            $issuer->value()
        );

        $subject = $principal->attributes()->get(
            'federation.subject'
        );
        self::assertNotNull($subject);
        self::assertSame(
            'subject-123',
            $subject->value()
        );

        $stableKey = $principal->attributes()->get(
            'federation.stable_key'
        );
        self::assertNotNull($stableKey);
        self::assertSame(
            $this->federatedIdentity()->stableKey(),
            $stableKey->value()
        );

        self::assertSame(
            60,
            $principal->evidence()->level()->value()
        );
    }

    public function testEmailClaimIsNeverUsedAsImplicitAccountLinkKey(): void
    {
        foreach ($this->i5ProductionFiles() as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                "claims()['email']",
                $source
            );
            self::assertStringNotContainsString(
                "claims()[\"email\"]",
                $source
            );
        }
    }

    public function testFederatedMappingDoesNotCreateSessionOrPersistLinksItself(): void
    {
        foreach ($this->i5ProductionFiles() as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'FederatedSessionEstablisherInterface',
                $source
            );
            self::assertStringNotContainsString(
                'setcookie(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'INSERT ',
                strtoupper($source)
            );
            self::assertStringNotContainsString(
                'UPDATE ',
                strtoupper($source)
            );
        }
    }

    /**
     * @return list<string>
     */
    private function i5ProductionFiles(): array
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc/Federation';

        return [
            $directory . '/LinkedLocalIdentity.php',
            $directory . '/FederatedProvisioningPolicy.php',
            $directory . '/FederatedAccountResolver.php',
            $directory . '/FederatedPrincipalFactory.php',
            $directory . '/FederatedAuthenticationMappingResult.php',
            $directory . '/FederatedAuthenticationMapper.php',
        ];
    }

    private function mapper(
        ?LinkedLocalIdentity $linked,
        bool $allowProvisioning
    ): FederatedAuthenticationMapper {
        $linkResolver = new class($linked) implements FederatedIdentityLinkResolverInterface {
            public function __construct(
                private ?LinkedLocalIdentity $linked
            ) {
            }

            public function resolve(
                OidcFederatedIdentity $federatedIdentity
            ): ?LinkedLocalIdentity {
                return $this->linked;
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

        return new FederatedAuthenticationMapper(
            new FederatedAccountResolver(
                $linkResolver,
                $provisioner,
                new FederatedProvisioningPolicy($allowProvisioning)
            ),
            new FederatedPrincipalFactory()
        );
    }

    private function federatedIdentity(): OidcFederatedIdentity
    {
        return new OidcFederatedIdentity(
            'https://identity.example',
            'subject-123',
            [
                'email' => 'user@example.test',
                'email_verified' => true,
            ]
        );
    }
}
