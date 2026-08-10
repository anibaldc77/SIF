<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialIssuanceProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProductCapabilities;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProductReadinessReport;
final class OpenId4VciCredentialIssuanceProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp245Surface(): void
    {
        $c = new CredentialIssuanceProductCapabilities();
        self::assertTrue($c->credentialOffers()); self::assertTrue($c->authorizationCodeGrant()); self::assertTrue($c->preAuthorizedCodeGrant());
        self::assertTrue($c->proofOfPossession()); self::assertTrue($c->batchIssuance()); self::assertTrue($c->deferredIssuance());
        self::assertTrue($c->issuerMetadataDiscovery()); self::assertTrue($c->transactionBinding()); self::assertTrue($c->notifications());
        self::assertTrue($c->operationalReadiness()); self::assertCount(10, $c->toArray());
    }
    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $p = new CredentialIssuanceProductProfile('openid4vci-default', new CredentialIssuanceProductCapabilities());
        self::assertSame('openid4vci-default',$p->name()); self::assertTrue($p->requireProofOfPossession()); self::assertTrue($p->requireReplayProtection()); self::assertTrue($p->requireTransactionBinding()); self::assertTrue($p->requireOperationalReadiness());
    }
    public function testReadinessReportCanRepresentReadyState(): void
    {
        $r=new CredentialIssuanceProductReadinessReport(true); self::assertTrue($r->ready()); self::assertSame([],$r->blockingIssues()); self::assertSame([],$r->warnings());
    }
    public function testReadinessReportCanRepresentBlockingState(): void
    {
        $r=new CredentialIssuanceProductReadinessReport(false,['proof_replay_store_unavailable'],['notification delivery policy requires review']);
        self::assertFalse($r->ready()); self::assertSame(['proof_replay_store_unavailable'],$r->blockingIssues()); self::assertSame(['notification delivery policy requires review'],$r->warnings());
    }
    public function testReadinessEvaluatorContractIsTyped(): void
    {
        $m=new \ReflectionMethod(CredentialIssuanceProductReadinessEvaluatorInterface::class,'evaluate');
        self::assertSame(CredentialIssuanceProductReadinessReport::class,(string)$m->getReturnType()); self::assertSame(CredentialIssuanceProductProfile::class,(string)$m->getParameters()[0]->getType());
    }
    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([CredentialIssuanceProductCapabilities::class,CredentialIssuanceProductProfile::class,CredentialIssuanceProductReadinessReport::class,CredentialIssuanceProductReadinessEvaluatorInterface::class] as $class) {
            $reflection=new \ReflectionClass($class); $source=file_get_contents((string)$reflection->getFileName()); self::assertIsString($source);
            self::assertStringNotContainsString('PDO',$source); self::assertStringNotContainsString('Redis',$source); self::assertStringNotContainsString('curl_',strtolower($source)); self::assertStringNotContainsString('Guzzle',$source); self::assertStringNotContainsString('Symfony',$source); self::assertStringNotContainsString('Laravel',$source);
        }
    }
}
