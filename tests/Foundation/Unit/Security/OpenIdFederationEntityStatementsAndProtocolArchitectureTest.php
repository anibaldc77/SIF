<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationResolverInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementResolverInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationTrustBridgeInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatementKind;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

final class OpenIdFederationEntityStatementsAndProtocolArchitectureTest extends TestCase
{
    private function entityConfigurationStatement(): OpenIdFederationEntityStatement
    {
        return new OpenIdFederationEntityStatement(
            OpenIdFederationEntityStatementKind::EntityConfiguration,
            'https://entity.example.test',
            'https://entity.example.test',
            new DateTimeImmutable('2026-08-12T15:00:00Z'),
            new DateTimeImmutable('2026-08-12T16:00:00Z'),
            ['https://superior.example.test'],
            ['federation_entity' => ['federation_fetch_endpoint' => 'https://entity.example.test/fetch']],
            [],
            ['trust-mark-001']
        );
    }

    public function testStatementKindsAreExplicit(): void
    {
        self::assertSame(
            'entity_configuration',
            OpenIdFederationEntityStatementKind::EntityConfiguration->value
        );
        self::assertSame(
            'subordinate_statement',
            OpenIdFederationEntityStatementKind::SubordinateStatement->value
        );
    }

    public function testEntityConfigurationIsSelfIssuedAndKeepsProtocolClaimsExplicit(): void
    {
        $statement = $this->entityConfigurationStatement();

        self::assertTrue($statement->isSelfIssued());
        self::assertSame($statement->issuer(), $statement->subject());
        self::assertSame(
            ['https://superior.example.test'],
            $statement->authorityHints()
        );
        self::assertSame(
            ['trust-mark-001'],
            $statement->trustMarkIds()
        );
    }

    public function testEntityConfigurationRejectsNonSelfIssuedStatement(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenIdFederationEntityStatement(
            OpenIdFederationEntityStatementKind::EntityConfiguration,
            'https://issuer.example.test',
            'https://subject.example.test',
            new DateTimeImmutable('2026-08-12T15:00:00Z'),
            new DateTimeImmutable('2026-08-12T16:00:00Z')
        );
    }

    public function testEntityConfigurationWrapsOnlyEntityConfigurationKind(): void
    {
        $configuration = new OpenIdFederationEntityConfiguration(
            $this->entityConfigurationStatement(),
            ['organization_name' => 'Example Federation Entity']
        );

        self::assertSame(
            'https://entity.example.test',
            $configuration->entityId()
        );
        self::assertSame(
            'Example Federation Entity',
            $configuration->federationEntityMetadata()['organization_name']
        );
    }

    public function testSubordinateStatementKeepsSuperiorAndSubordinateExplicit(): void
    {
        $statement = new OpenIdFederationEntityStatement(
            OpenIdFederationEntityStatementKind::SubordinateStatement,
            'https://superior.example.test',
            'https://subordinate.example.test',
            new DateTimeImmutable('2026-08-12T15:00:00Z'),
            new DateTimeImmutable('2026-08-12T16:00:00Z'),
            [],
            [],
            ['openid_credential_issuer' => ['value' => ['required']]],
            ['issuer-accreditation']
        );

        $subordinate = new OpenIdFederationSubordinateStatement($statement);

        self::assertSame(
            'https://superior.example.test',
            $subordinate->superiorEntityId()
        );
        self::assertSame(
            'https://subordinate.example.test',
            $subordinate->subordinateEntityId()
        );
    }

    public function testStatementValidationResultRepresentsValidAndBlockedStates(): void
    {
        $valid = new OpenIdFederationStatementValidationResult(true);

        self::assertTrue($valid->valid());
        self::assertSame([], $valid->violations());

        $blocked = new OpenIdFederationStatementValidationResult(
            false,
            ['signature_invalid'],
            ['statement expires soon']
        );

        self::assertFalse($blocked->valid());
        self::assertSame(['signature_invalid'], $blocked->violations());
        self::assertSame(
            ['statement expires soon'],
            $blocked->warnings()
        );
    }

    public function testProtocolContractsAreTypedAndSeparated(): void
    {
        $configuration = new \ReflectionMethod(
            OpenIdFederationEntityConfigurationResolverInterface::class,
            'resolve'
        );
        $subordinate = new \ReflectionMethod(
            OpenIdFederationSubordinateStatementResolverInterface::class,
            'resolve'
        );
        $verify = new \ReflectionMethod(
            OpenIdFederationEntityStatementVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            OpenIdFederationEntityConfiguration::class,
            (string) $configuration->getReturnType()
        );
        self::assertSame(
            OpenIdFederationSubordinateStatement::class,
            (string) $subordinate->getReturnType()
        );
        self::assertSame(
            OpenIdFederationStatementValidationResult::class,
            (string) $verify->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(
                OpenIdFederationTrustBridgeInterface::class
            ))->isInterface()
        );
    }

    public function testArchitectureUsesWp250AsTrustBoundaryInsteadOfDuplicatingIt(): void
    {
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustAnchorResolverInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class
            )
        );
    }

    public function testProtocolArchitectureRemainsJwtCryptoTransportAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationEntityConfigurationResolverInterface::class,
            OpenIdFederationSubordinateStatementResolverInterface::class,
            OpenIdFederationEntityStatementVerifierInterface::class,
            OpenIdFederationTrustBridgeInterface::class,
            OpenIdFederationEntityStatement::class,
            OpenIdFederationEntityConfiguration::class,
            OpenIdFederationSubordinateStatement::class,
            OpenIdFederationStatementValidationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
