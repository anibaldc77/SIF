<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationValidationPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatementKind;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;
use Sif\Foundation\Security\OpenIdFederation\Validation\DefaultOpenIdFederationEntityConfigurationValidator;
use Sif\Foundation\Security\OpenIdFederation\Validation\DefaultOpenIdFederationSubordinateStatementValidator;

final class OpenIdFederationEntityConfigurationAndSubordinateStatementValidationTest extends TestCase
{
    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-12T15:30:00Z');
    }

    /**
     * @param array<string, mixed> $metadataPolicy
     */
    private function entityConfiguration(
        array $metadataPolicy = []
    ): OpenIdFederationEntityConfiguration {
        return new OpenIdFederationEntityConfiguration(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::EntityConfiguration,
                'https://entity.example.test',
                'https://entity.example.test',
                new DateTimeImmutable('2026-08-12T15:00:00Z'),
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                ['https://superior.example.test'],
                ['federation_entity' => ['organization_name' => 'Example']],
                $metadataPolicy
            )
        );
    }

    /**
     * @param list<string> $authorityHints
     */
    private function subordinate(
        array $authorityHints = []
    ): OpenIdFederationSubordinateStatement {
        return new OpenIdFederationSubordinateStatement(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::SubordinateStatement,
                'https://superior.example.test',
                'https://subordinate.example.test',
                new DateTimeImmutable('2026-08-12T15:00:00Z'),
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                $authorityHints,
                [],
                ['openid_credential_issuer' => ['credential_endpoint' => ['value' => 'https://issuer.example.test/credential']]]
            )
        );
    }

    public function testEntityConfigurationValidatorAcceptsValidConfiguration(): void
    {
        $validator = new DefaultOpenIdFederationEntityConfigurationValidator();

        $result = $validator->validate(
            $this->entityConfiguration(),
            new OpenIdFederationStatementValidationContext(
                $this->at(),
                'https://entity.example.test'
            )
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testEntityConfigurationRejectsMetadataPolicy(): void
    {
        $validator = new DefaultOpenIdFederationEntityConfigurationValidator();

        $result = $validator->validate(
            $this->entityConfiguration([
                'openid_relying_party' => [
                    'grant_types' => ['subset_of' => ['authorization_code']],
                ],
            ]),
            new OpenIdFederationStatementValidationContext($this->at())
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'metadata_policy_not_allowed_in_entity_configuration',
            $result->violations()
        );
    }

    public function testEntityConfigurationRejectsUnexpectedEntityIdentifier(): void
    {
        $validator = new DefaultOpenIdFederationEntityConfigurationValidator();

        $result = $validator->validate(
            $this->entityConfiguration(),
            new OpenIdFederationStatementValidationContext(
                $this->at(),
                'https://other.example.test'
            )
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'unexpected_entity_identifier',
            $result->violations()
        );
    }

    public function testEntityConfigurationRejectsExpiredStatement(): void
    {
        $validator = new DefaultOpenIdFederationEntityConfigurationValidator();

        $result = $validator->validate(
            $this->entityConfiguration(),
            new OpenIdFederationStatementValidationContext(
                new DateTimeImmutable('2026-08-12T16:00:01Z')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'entity_statement_expired',
            $result->violations()
        );
    }

    public function testSubordinateValidatorAcceptsValidStatement(): void
    {
        $validator = new DefaultOpenIdFederationSubordinateStatementValidator();

        $result = $validator->validate(
            $this->subordinate(),
            new OpenIdFederationStatementValidationContext(
                $this->at(),
                'https://subordinate.example.test'
            )
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testSubordinateRejectsAuthorityHints(): void
    {
        $validator = new DefaultOpenIdFederationSubordinateStatementValidator();

        $result = $validator->validate(
            $this->subordinate(['https://another.example.test']),
            new OpenIdFederationStatementValidationContext($this->at())
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'authority_hints_not_allowed_in_subordinate_statement',
            $result->violations()
        );
    }

    public function testSubordinateRejectsStatementIssuedInFuture(): void
    {
        $statement = new OpenIdFederationSubordinateStatement(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::SubordinateStatement,
                'https://superior.example.test',
                'https://subordinate.example.test',
                new DateTimeImmutable('2026-08-12T15:31:00Z'),
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                [],
                [],
                []
            )
        );

        $validator = new DefaultOpenIdFederationSubordinateStatementValidator();

        $result = $validator->validate(
            $statement,
            new OpenIdFederationStatementValidationContext($this->at())
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'entity_statement_issued_in_future',
            $result->violations()
        );
    }

    public function testValidationContractsAreTypedAndSeparated(): void
    {
        $entity = new \ReflectionMethod(
            OpenIdFederationEntityConfigurationValidationPolicyInterface::class,
            'validate'
        );
        $subordinate = new \ReflectionMethod(
            OpenIdFederationSubordinateStatementValidationPolicyInterface::class,
            'validate'
        );

        self::assertSame(
            OpenIdFederationStatementValidationResult::class,
            (string) $entity->getReturnType()
        );
        self::assertSame(
            OpenIdFederationStatementValidationResult::class,
            (string) $subordinate->getReturnType()
        );
    }

    public function testArchitecturePreservesI1AndWp250Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationResolverInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustBridgeInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testSemanticValidationLayerRemainsCryptoTransportAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationEntityConfigurationValidationPolicyInterface::class,
            OpenIdFederationSubordinateStatementValidationPolicyInterface::class,
            DefaultOpenIdFederationEntityConfigurationValidator::class,
            DefaultOpenIdFederationSubordinateStatementValidator::class,
            OpenIdFederationStatementValidationContext::class,
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
