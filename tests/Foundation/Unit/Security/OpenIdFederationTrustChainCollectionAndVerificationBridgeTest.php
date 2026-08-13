<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationCredentialTrustChainBridgeInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainCollectorInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatementKind;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;
use Sif\Foundation\Security\OpenIdFederation\TrustChain\DefaultOpenIdFederationTrustChainValidator;
use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChain;
use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChainValidationResult;

final class OpenIdFederationTrustChainCollectionAndVerificationBridgeTest extends TestCase
{
    private function configuration(string $entityId): OpenIdFederationEntityConfiguration
    {
        return new OpenIdFederationEntityConfiguration(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::EntityConfiguration,
                $entityId,
                $entityId,
                new DateTimeImmutable('2026-08-13T10:00:00Z'),
                new DateTimeImmutable('2026-08-13T12:00:00Z')
            )
        );
    }

    private function subordinate(
        string $superior,
        string $subordinate
    ): OpenIdFederationSubordinateStatement {
        return new OpenIdFederationSubordinateStatement(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::SubordinateStatement,
                $superior,
                $subordinate,
                new DateTimeImmutable('2026-08-13T10:00:00Z'),
                new DateTimeImmutable('2026-08-13T12:00:00Z')
            )
        );
    }

    public function testTrustChainKeepsLeafStatementsAnchorAndDepthExplicit(): void
    {
        $chain = new OpenIdFederationTrustChain(
            $this->configuration('https://leaf.example.test'),
            [
                $this->subordinate('https://intermediate.example.test', 'https://leaf.example.test'),
                $this->subordinate('https://anchor.example.test', 'https://intermediate.example.test'),
            ],
            $this->configuration('https://anchor.example.test')
        );

        self::assertSame('https://leaf.example.test', $chain->leafEntityId());
        self::assertSame('https://anchor.example.test', $chain->trustAnchorEntityId());
        self::assertSame(3, $chain->depth());
        self::assertCount(2, $chain->subordinateStatements());
    }

    public function testValidatorAcceptsContinuousCurrentChain(): void
    {
        $chain = new OpenIdFederationTrustChain(
            $this->configuration('https://leaf.example.test'),
            [
                $this->subordinate('https://intermediate.example.test', 'https://leaf.example.test'),
                $this->subordinate('https://anchor.example.test', 'https://intermediate.example.test'),
            ],
            $this->configuration('https://anchor.example.test')
        );

        $result = (new DefaultOpenIdFederationTrustChainValidator())->validate(
            $chain,
            new OpenIdFederationStatementValidationContext(
                new DateTimeImmutable('2026-08-13T11:00:00Z'),
                'https://leaf.example.test'
            )
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testValidatorRejectsSubjectDiscontinuity(): void
    {
        $chain = new OpenIdFederationTrustChain(
            $this->configuration('https://leaf.example.test'),
            [
                $this->subordinate('https://intermediate.example.test', 'https://different-leaf.example.test'),
                $this->subordinate('https://anchor.example.test', 'https://intermediate.example.test'),
            ],
            $this->configuration('https://anchor.example.test')
        );

        $result = (new DefaultOpenIdFederationTrustChainValidator())->validate(
            $chain,
            new OpenIdFederationStatementValidationContext(
                new DateTimeImmutable('2026-08-13T11:00:00Z')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains('trust_chain_subject_discontinuity', $result->violations());
    }

    public function testValidatorRejectsChainThatDoesNotTerminateAtAnchor(): void
    {
        $chain = new OpenIdFederationTrustChain(
            $this->configuration('https://leaf.example.test'),
            [
                $this->subordinate('https://intermediate.example.test', 'https://leaf.example.test'),
            ],
            $this->configuration('https://anchor.example.test')
        );

        $result = (new DefaultOpenIdFederationTrustChainValidator())->validate(
            $chain,
            new OpenIdFederationStatementValidationContext(
                new DateTimeImmutable('2026-08-13T11:00:00Z')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains('trust_chain_does_not_terminate_at_anchor', $result->violations());
    }

    public function testValidatorRejectsExpiredStatement(): void
    {
        $expired = new OpenIdFederationSubordinateStatement(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::SubordinateStatement,
                'https://anchor.example.test',
                'https://leaf.example.test',
                new DateTimeImmutable('2026-08-13T09:00:00Z'),
                new DateTimeImmutable('2026-08-13T10:30:00Z')
            )
        );

        $chain = new OpenIdFederationTrustChain(
            $this->configuration('https://leaf.example.test'),
            [$expired],
            $this->configuration('https://anchor.example.test')
        );

        $result = (new DefaultOpenIdFederationTrustChainValidator())->validate(
            $chain,
            new OpenIdFederationStatementValidationContext(
                new DateTimeImmutable('2026-08-13T11:00:00Z')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains('trust_chain_statement_expired', $result->violations());
    }

    public function testTrustChainContractsAreTypedAndSeparated(): void
    {
        $collect = new \ReflectionMethod(
            OpenIdFederationTrustChainCollectorInterface::class,
            'collect'
        );
        $validate = new \ReflectionMethod(
            OpenIdFederationTrustChainValidationPolicyInterface::class,
            'validate'
        );
        $map = new \ReflectionMethod(
            OpenIdFederationCredentialTrustChainBridgeInterface::class,
            'map'
        );

        self::assertSame(OpenIdFederationTrustChain::class, (string) $collect->getReturnType());
        self::assertSame(
            OpenIdFederationTrustChainValidationResult::class,
            (string) $validate->getReturnType()
        );
        self::assertSame(
            \Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChain::class,
            (string) $map->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI5AndWp250Boundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationResolverInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementResolverInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyResolverInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testFederationTrustChainLayerRemainsTransportCryptoAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationTrustChainCollectorInterface::class,
            OpenIdFederationTrustChainValidationPolicyInterface::class,
            OpenIdFederationCredentialTrustChainBridgeInterface::class,
            OpenIdFederationTrustChain::class,
            OpenIdFederationTrustChainValidationResult::class,
            DefaultOpenIdFederationTrustChainValidator::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

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
