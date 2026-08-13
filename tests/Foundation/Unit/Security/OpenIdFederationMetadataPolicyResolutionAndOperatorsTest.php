<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyApplicatorInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyResolverInterface;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\DefaultOpenIdFederationMetadataPolicyApplicator;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataParameterPolicy;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicy;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicyApplicationResult;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicyOperator;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationResolvedMetadataPolicy;

final class OpenIdFederationMetadataPolicyResolutionAndOperatorsTest extends TestCase
{
    public function testStandardOperatorsAreExplicit(): void
    {
        self::assertSame('value', OpenIdFederationMetadataPolicyOperator::Value->value);
        self::assertSame('add', OpenIdFederationMetadataPolicyOperator::Add->value);
        self::assertSame('default', OpenIdFederationMetadataPolicyOperator::Default->value);
        self::assertSame('one_of', OpenIdFederationMetadataPolicyOperator::OneOf->value);
        self::assertSame('subset_of', OpenIdFederationMetadataPolicyOperator::SubsetOf->value);
        self::assertSame('superset_of', OpenIdFederationMetadataPolicyOperator::SupersetOf->value);
        self::assertSame('essential', OpenIdFederationMetadataPolicyOperator::Essential->value);
    }

    public function testParameterPolicyKeepsOperatorsExplicit(): void
    {
        $policy = new OpenIdFederationMetadataParameterPolicy([
            'subset_of' => ['RS256', 'ES256'],
            'essential' => true,
        ]);

        self::assertTrue(
            $policy->has(OpenIdFederationMetadataPolicyOperator::SubsetOf)
        );
        self::assertSame(
            ['RS256', 'ES256'],
            $policy->value(OpenIdFederationMetadataPolicyOperator::SubsetOf)
        );
    }

    public function testValueOperatorOverridesMetadataParameter(): void
    {
        $result = $this->apply(
            ['response_types_supported' => ['code']],
            new OpenIdFederationMetadataParameterPolicy([
                'value' => ['code', 'code id_token'],
            ]),
            'response_types_supported'
        );

        self::assertTrue($result->valid());
        self::assertSame(
            ['code', 'code id_token'],
            $result->metadata()['response_types_supported']
        );
    }

    public function testAddOperatorAddsUniqueValues(): void
    {
        $result = $this->apply(
            ['grant_types_supported' => ['authorization_code']],
            new OpenIdFederationMetadataParameterPolicy([
                'add' => ['refresh_token', 'authorization_code'],
            ]),
            'grant_types_supported'
        );

        self::assertTrue($result->valid());
        self::assertSame(
            ['authorization_code', 'refresh_token'],
            $result->metadata()['grant_types_supported']
        );
    }

    public function testDefaultOperatorPopulatesAbsentParameter(): void
    {
        $result = $this->apply(
            [],
            new OpenIdFederationMetadataParameterPolicy([
                'default' => 'ES256',
            ]),
            'request_object_signing_alg'
        );

        self::assertTrue($result->valid());
        self::assertSame(
            'ES256',
            $result->metadata()['request_object_signing_alg']
        );
    }

    public function testOneOfRejectsUnsupportedValue(): void
    {
        $result = $this->apply(
            ['token_endpoint_auth_method' => 'client_secret_basic'],
            new OpenIdFederationMetadataParameterPolicy([
                'one_of' => ['private_key_jwt', 'client_secret_jwt'],
            ]),
            'token_endpoint_auth_method'
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'token_endpoint_auth_method:one_of_failed',
            $result->violations()
        );
    }

    public function testSubsetOfIntersectsMetadataValues(): void
    {
        $result = $this->apply(
            [
                'id_token_signing_alg_values_supported' => [
                    'RS256',
                    'ES256',
                    'HS256',
                ],
            ],
            new OpenIdFederationMetadataParameterPolicy([
                'subset_of' => ['RS256', 'ES256'],
            ]),
            'id_token_signing_alg_values_supported'
        );

        self::assertTrue($result->valid());
        self::assertSame(
            ['RS256', 'ES256'],
            $result->metadata()['id_token_signing_alg_values_supported']
        );
    }

    public function testSupersetOfRejectsMissingRequiredValue(): void
    {
        $result = $this->apply(
            ['token_endpoint_auth_methods_supported' => ['client_secret_jwt']],
            new OpenIdFederationMetadataParameterPolicy([
                'superset_of' => ['private_key_jwt'],
            ]),
            'token_endpoint_auth_methods_supported'
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'token_endpoint_auth_methods_supported:superset_of_failed',
            $result->violations()
        );
    }

    public function testEssentialRejectsMissingMetadataParameter(): void
    {
        $result = $this->apply(
            [],
            new OpenIdFederationMetadataParameterPolicy([
                'essential' => true,
            ]),
            'jwks_uri'
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'jwks_uri:essential_missing',
            $result->violations()
        );
    }

    public function testPolicyAndResolutionModelsRemainSeparate(): void
    {
        $policy = new OpenIdFederationMetadataPolicy([
            'openid_provider' => [
                'jwks_uri' => new OpenIdFederationMetadataParameterPolicy([
                    'essential' => true,
                ]),
            ],
        ], ['custom_operator']);

        $resolved = new OpenIdFederationResolvedMetadataPolicy(
            $policy,
            [
                'https://superior.example.test',
                'https://anchor.example.test',
            ]
        );

        self::assertSame($policy, $resolved->policy());
        self::assertCount(2, $resolved->sourceEntityIds());
        self::assertSame(['custom_operator'], $policy->criticalOperators());
    }

    public function testMetadataPolicyContractsAreTypedAndSeparated(): void
    {
        $apply = new \ReflectionMethod(
            OpenIdFederationMetadataPolicyApplicatorInterface::class,
            'apply'
        );
        $resolve = new \ReflectionMethod(
            OpenIdFederationMetadataPolicyResolverInterface::class,
            'resolve'
        );

        self::assertSame(
            OpenIdFederationMetadataPolicyApplicationResult::class,
            (string) $apply->getReturnType()
        );
        self::assertSame(
            OpenIdFederationResolvedMetadataPolicy::class,
            (string) $resolve->getReturnType()
        );
    }

    public function testArchitecturePreservesI1ToI3AndWp250Boundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationFetchProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationResolveProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testMetadataPolicyLayerRemainsTransportCryptoAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationMetadataPolicyApplicatorInterface::class,
            OpenIdFederationMetadataPolicyResolverInterface::class,
            DefaultOpenIdFederationMetadataPolicyApplicator::class,
            OpenIdFederationMetadataParameterPolicy::class,
            OpenIdFederationMetadataPolicy::class,
            OpenIdFederationResolvedMetadataPolicy::class,
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

    /**
     * @param array<string, mixed> $metadata
     */
    private function apply(
        array $metadata,
        OpenIdFederationMetadataParameterPolicy $parameterPolicy,
        string $parameter
    ): OpenIdFederationMetadataPolicyApplicationResult {
        $policy = new OpenIdFederationMetadataPolicy([
            'openid_provider' => [
                $parameter => $parameterPolicy,
            ],
        ]);

        return (new DefaultOpenIdFederationMetadataPolicyApplicator())->apply(
            $metadata,
            'openid_provider',
            $policy
        );
    }
}
