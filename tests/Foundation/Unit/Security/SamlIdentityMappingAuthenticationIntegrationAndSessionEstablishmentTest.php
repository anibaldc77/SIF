<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SamlSessionEstablisherInterface;
use Sif\Foundation\Security\Saml\DefaultSamlIdentityMapper;
use Sif\Foundation\Security\Saml\SamlAssertion;
use Sif\Foundation\Security\Saml\SamlAssertionConditions;
use Sif\Foundation\Security\Saml\SamlAssertionId;
use Sif\Foundation\Security\Saml\SamlAuthenticatedIdentity;
use Sif\Foundation\Security\Saml\SamlAuthenticationCoordinator;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlNameId;

final class SamlIdentityMappingAuthenticationIntegrationAndSessionEstablishmentTest extends TestCase
{
    public function testDefaultMapperUsesNameIdAsSubjectIdentifier(): void
    {
        $identity = (new DefaultSamlIdentityMapper())->map(
            $this->assertion()
        );

        self::assertSame(
            'user@example.com',
            $identity->subjectIdentifier()
        );
        self::assertSame(
            'https://idp.example/saml',
            $identity->issuer()->value()
        );
    }

    public function testMapperPreservesExplicitAttributes(): void
    {
        $identity = (new DefaultSamlIdentityMapper())->map(
            $this->assertion(),
            [
                'email' => 'user@example.com',
                'roles' => ['editor', 'reviewer'],
            ]
        );

        self::assertSame(
            'user@example.com',
            $identity->attributes()['email']
        );
        self::assertSame(
            ['editor', 'reviewer'],
            $identity->attributes()['roles']
        );
    }

    public function testCoordinatorEstablishesSessionOnlyAfterIdentityMapping(): void
    {
        $session = new RecordingSamlSessionEstablisher();

        $result = (new SamlAuthenticationCoordinator(
            new DefaultSamlIdentityMapper(),
            $session
        ))->authenticate(
            $this->assertion(),
            ['department' => 'legal']
        );

        self::assertTrue($result->sessionEstablished());
        self::assertSame(1, $session->calls());
        self::assertSame(
            'user@example.com',
            $session->lastIdentity()?->subjectIdentifier()
        );
    }

    public function testCoordinatorReturnsMappedIdentity(): void
    {
        $result = (new SamlAuthenticationCoordinator(
            new DefaultSamlIdentityMapper(),
            new RecordingSamlSessionEstablisher()
        ))->authenticate(
            $this->assertion()
        );

        self::assertSame(
            'user@example.com',
            $result->identity()->subjectIdentifier()
        );
    }

    public function testSamlParsingLayerDoesNotCreateSessionDirectly(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Saml';

        foreach (glob($directory . '/*Parser.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'session_start',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'setcookie',
                strtolower($source)
            );
        }
    }

    public function testAuthenticationIntegrationRemainsFrameworkNeutral(): void
    {
        foreach ([
            SamlAuthenticationCoordinator::class,
            DefaultSamlIdentityMapper::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('OneLogin', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
            self::assertStringNotContainsString('session_start', strtolower($source));
        }
    }

    private function assertion(): SamlAssertion
    {
        return new SamlAssertion(
            new SamlAssertionId(
                '_assertion-000000000001'
            ),
            new DateTimeImmutable(
                '2026-08-07T23:00:00Z'
            ),
            new SamlEntityId(
                'https://idp.example/saml'
            ),
            new SamlNameId(
                'user@example.com',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'
            ),
            new SamlAssertionConditions(
                null,
                null,
                [
                    new SamlEntityId(
                        'https://sp.example/saml'
                    ),
                ]
            )
        );
    }
}

final class RecordingSamlSessionEstablisher implements SamlSessionEstablisherInterface
{
    private int $calls = 0;
    private ?SamlAuthenticatedIdentity $lastIdentity = null;

    public function establish(
        SamlAuthenticatedIdentity $identity
    ): void {
        $this->calls++;
        $this->lastIdentity = $identity;
    }

    public function calls(): int
    {
        return $this->calls;
    }

    public function lastIdentity(): ?SamlAuthenticatedIdentity
    {
        return $this->lastIdentity;
    }
}
