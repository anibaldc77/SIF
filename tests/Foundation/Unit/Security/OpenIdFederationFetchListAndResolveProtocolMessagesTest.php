<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationFetchProtocolInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationListProtocolInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationResolveProtocolInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatementKind;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationFetchRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationFetchResponse;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationListRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationListResponse;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationResolveRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationResolveResponse;

final class OpenIdFederationFetchListAndResolveProtocolMessagesTest extends TestCase
{
    public function testFetchRequestKeepsIssuerAndSubjectExplicit(): void
    {
        $request = new OpenIdFederationFetchRequest(
            'https://superior.example.test',
            'https://subordinate.example.test'
        );

        self::assertSame(
            'https://superior.example.test',
            $request->issuerEntityId()
        );
        self::assertSame(
            'https://subordinate.example.test',
            $request->subjectEntityId()
        );
    }

    public function testFetchResponseWrapsSubordinateStatement(): void
    {
        $statement = new OpenIdFederationSubordinateStatement(
            new OpenIdFederationEntityStatement(
                OpenIdFederationEntityStatementKind::SubordinateStatement,
                'https://superior.example.test',
                'https://subordinate.example.test',
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                new DateTimeImmutable('2026-08-12T17:00:00Z')
            )
        );

        $response = new OpenIdFederationFetchResponse($statement);

        self::assertSame($statement, $response->statement());
    }

    public function testListRequestKeepsOptionalFiltersExplicit(): void
    {
        $request = new OpenIdFederationListRequest(
            'https://superior.example.test',
            'openid_provider',
            'https://trustmark.example.test/qualified'
        );

        self::assertSame(
            'https://superior.example.test',
            $request->issuerEntityId()
        );
        self::assertSame('openid_provider', $request->entityType());
        self::assertSame(
            'https://trustmark.example.test/qualified',
            $request->trustMarked()
        );
    }

    public function testListResponseKeepsImmediateSubordinateIdentifiersExplicit(): void
    {
        $response = new OpenIdFederationListResponse([
            'https://subordinate-a.example.test',
            'https://subordinate-b.example.test',
        ]);

        self::assertSame(
            [
                'https://subordinate-a.example.test',
                'https://subordinate-b.example.test',
            ],
            $response->entityIds()
        );
    }

    public function testResolveRequestKeepsSubjectTrustAnchorsAndEntityTypesExplicit(): void
    {
        $request = new OpenIdFederationResolveRequest(
            'https://subject.example.test',
            [
                'https://anchor-a.example.test',
                'https://anchor-b.example.test',
            ],
            [
                'openid_provider',
                'openid_credential_issuer',
            ]
        );

        self::assertSame(
            'https://subject.example.test',
            $request->subjectEntityId()
        );
        self::assertCount(2, $request->trustAnchorEntityIds());
        self::assertSame(
            ['openid_provider', 'openid_credential_issuer'],
            $request->entityTypes()
        );
    }

    public function testResolveResponseKeepsResolvedMetadataTrustChainAndVerifiedMarksExplicit(): void
    {
        $response = new OpenIdFederationResolveResponse(
            'https://subject.example.test',
            'https://anchor.example.test',
            [
                'openid_provider' => [
                    'issuer' => 'https://subject.example.test',
                ],
            ],
            ['statement-001', 'statement-002'],
            ['trust-mark-001']
        );

        self::assertSame(
            'https://subject.example.test',
            $response->subjectEntityId()
        );
        self::assertSame(
            'https://anchor.example.test',
            $response->trustAnchorEntityId()
        );
        self::assertSame(
            'https://subject.example.test',
            $response->resolvedMetadata()['openid_provider']['issuer']
        );
        self::assertCount(2, $response->trustChainStatementIds());
        self::assertSame(
            ['trust-mark-001'],
            $response->verifiedTrustMarkIds()
        );
    }

    public function testProtocolContractsAreTypedAndSeparated(): void
    {
        $fetch = new \ReflectionMethod(
            OpenIdFederationFetchProtocolInterface::class,
            'fetch'
        );
        $list = new \ReflectionMethod(
            OpenIdFederationListProtocolInterface::class,
            'list'
        );
        $resolve = new \ReflectionMethod(
            OpenIdFederationResolveProtocolInterface::class,
            'resolve'
        );

        self::assertSame(
            OpenIdFederationFetchResponse::class,
            (string) $fetch->getReturnType()
        );
        self::assertSame(
            OpenIdFederationListResponse::class,
            (string) $list->getReturnType()
        );
        self::assertSame(
            OpenIdFederationResolveResponse::class,
            (string) $resolve->getReturnType()
        );
    }

    public function testArchitecturePreservesI1I2AndWp250Boundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testProtocolMessageLayerRemainsHttpJwtCryptoAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationFetchProtocolInterface::class,
            OpenIdFederationListProtocolInterface::class,
            OpenIdFederationResolveProtocolInterface::class,
            OpenIdFederationFetchRequest::class,
            OpenIdFederationFetchResponse::class,
            OpenIdFederationListRequest::class,
            OpenIdFederationListResponse::class,
            OpenIdFederationResolveRequest::class,
            OpenIdFederationResolveResponse::class,
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
