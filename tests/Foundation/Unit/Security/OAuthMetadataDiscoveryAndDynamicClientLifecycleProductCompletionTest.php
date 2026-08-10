<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthMetadataProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataProductCapabilities;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataProductProfile;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataProductReadinessReport;
final class OAuthMetadataDiscoveryAndDynamicClientLifecycleProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp241Surface(): void { $c=new OAuthMetadataProductCapabilities(); self::assertTrue($c->authorizationServerMetadata()); self::assertTrue($c->protectedResourceMetadata()); self::assertTrue($c->dynamicClientRegistration()); self::assertTrue($c->clientRegistrationManagement()); self::assertTrue($c->softwareStatements()); self::assertTrue($c->issuerValidation()); self::assertTrue($c->discoveryResolution()); self::assertCount(7,$c->toArray()); }
    public function testProductProfileMakesSecurityRequirementsExplicit(): void { $p=new OAuthMetadataProductProfile('oauth-metadata-default',new OAuthMetadataProductCapabilities()); self::assertSame('oauth-metadata-default',$p->name()); self::assertTrue($p->requireExactIssuerMatch()); self::assertTrue($p->requireHttpsDiscovery()); self::assertTrue($p->requireFreshMetadata()); self::assertTrue($p->allowDynamicRegistration()); }
    public function testReadinessReportCanRepresentReadyProduct(): void { $r=new OAuthMetadataProductReadinessReport(true); self::assertTrue($r->ready()); self::assertSame([],$r->missingCapabilities()); self::assertSame([],$r->warnings()); }
    public function testReadinessReportCanRepresentMissingCapabilities(): void { $r=new OAuthMetadataProductReadinessReport(false,['discovery_resolution'],['metadata cache unavailable']); self::assertFalse($r->ready()); self::assertSame(['discovery_resolution'],$r->missingCapabilities()); self::assertSame(['metadata cache unavailable'],$r->warnings()); }
    public function testReadinessEvaluatorContractIsTyped(): void { $m=new \ReflectionMethod(OAuthMetadataProductReadinessEvaluatorInterface::class,'evaluate'); self::assertSame(OAuthMetadataProductReadinessReport::class,(string)$m->getReturnType()); $p=$m->getParameters(); self::assertCount(1,$p); self::assertSame(OAuthMetadataProductProfile::class,(string)$p[0]->getType()); }
    public function testProductCompletionBoundaryRemainsInfrastructureNeutral(): void { foreach ([OAuthMetadataProductReadinessEvaluatorInterface::class,OAuthMetadataProductCapabilities::class,OAuthMetadataProductProfile::class,OAuthMetadataProductReadinessReport::class] as $class) { $r=new \ReflectionClass($class); $s=file_get_contents((string)$r->getFileName()); self::assertIsString($s); self::assertStringNotContainsString('PDO',$s); self::assertStringNotContainsString('Redis',$s); self::assertStringNotContainsString('curl_',strtolower($s)); self::assertStringNotContainsString('Symfony',$s); self::assertStringNotContainsString('Laravel',$s); } }
}
