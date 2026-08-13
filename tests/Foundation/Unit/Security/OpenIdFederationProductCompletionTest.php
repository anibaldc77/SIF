<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\OpenIdFederation\Product\OpenIdFederationProductCapabilities;
use Sif\Foundation\Security\OpenIdFederation\Product\OpenIdFederationProductProfile;
use Sif\Foundation\Security\OpenIdFederation\Product\OpenIdFederationProductReadinessReport;
final class OpenIdFederationProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp251Surface(): void
    {
        $c=new OpenIdFederationProductCapabilities();
        self::assertTrue($c->entityStatements()); self::assertTrue($c->semanticValidation()); self::assertTrue($c->fetchListResolveProtocols()); self::assertTrue($c->metadataPolicy()); self::assertTrue($c->trustMarks()); self::assertTrue($c->federationTrustChains()); self::assertTrue($c->runtimeFreshnessAndResilience()); self::assertTrue($c->oidcWalletInteroperability()); self::assertCount(8,$c->toArray());
    }
    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $p=new OpenIdFederationProductProfile('openid-federation-default',new OpenIdFederationProductCapabilities());
        self::assertSame('openid-federation-default',$p->name()); self::assertTrue($p->requireVerifiedEntityStatements()); self::assertTrue($p->requireValidatedMetadataPolicy()); self::assertTrue($p->requireValidatedFederationTrustChain()); self::assertTrue($p->requireCurrentRuntimeEvidence()); self::assertTrue($p->requireCredentialTrustEnforcement());
    }
    public function testProductReadinessReportRepresentsReadyAndBlockedStates(): void
    {
        $ready=new OpenIdFederationProductReadinessReport(true); self::assertTrue($ready->ready());
        $blocked=new OpenIdFederationProductReadinessReport(false,['entity_statement_verifier_missing'],['stale fallback policy requires review']);
        self::assertFalse($blocked->ready()); self::assertSame(['entity_statement_verifier_missing'],$blocked->blockingIssues()); self::assertSame(['stale fallback policy requires review'],$blocked->warnings());
    }
    public function testProductReadinessEvaluatorContractIsTyped(): void
    {
        $m=new \ReflectionMethod(OpenIdFederationProductReadinessEvaluatorInterface::class,'evaluate');
        self::assertSame(OpenIdFederationProductProfile::class,(string)$m->getParameters()[0]->getType()); self::assertSame(OpenIdFederationProductReadinessReport::class,(string)$m->getReturnType());
    }
    public function testCompletionPreservesSpecializedFederationAndWp250Contracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationFetchProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationListProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationResolveProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyApplicatorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyResolverInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainCollectorInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationCredentialTrustChainBridgeInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFreshnessPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFailurePolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) { self::assertTrue(interface_exists($contract),$contract); }
    }
    public function testProductCompletionLayerRemainsTransportCryptoAndStorageNeutral(): void
    {
        foreach ([OpenIdFederationProductReadinessEvaluatorInterface::class,OpenIdFederationProductCapabilities::class,OpenIdFederationProductProfile::class,OpenIdFederationProductReadinessReport::class] as $class) {
            $r=new \ReflectionClass($class); $source=file_get_contents((string)$r->getFileName()); self::assertIsString($source);
            self::assertStringNotContainsString('PDO',$source); self::assertStringNotContainsString('Redis',$source); self::assertStringNotContainsString('Memcached',$source); self::assertStringNotContainsString('curl_',strtolower($source)); self::assertStringNotContainsString('Guzzle',$source); self::assertStringNotContainsString('openssl_',strtolower($source)); self::assertStringNotContainsString('Firebase',$source);
        }
    }
}
