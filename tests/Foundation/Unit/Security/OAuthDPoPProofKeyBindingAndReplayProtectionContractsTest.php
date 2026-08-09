<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthDPoPNonceServiceInterface;
use Sif\Foundation\Security\Contracts\OAuthDPoPReplayStoreInterface;
use Sif\Foundation\Security\Contracts\OAuthProofOfPossessionVerifierInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPProof;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationContext;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationResult;
final class OAuthDPoPProofKeyBindingAndReplayProtectionContractsTest extends TestCase
{
    public function testProofCarriesBindingData(): void
    {
        $p=new OAuthDPoPProof('a.b.c','POST','https://server.test/token',new DateTimeImmutable('2026-08-09T10:00:00Z'),'jti-1','thumb-1','ath-1','nonce-1');
        self::assertSame('POST',$p->httpMethod());
        self::assertSame('https://server.test/token',$p->httpUri());
        self::assertSame('jti-1',$p->tokenId());
        self::assertSame('thumb-1',$p->publicKeyThumbprint());
        self::assertSame('ath-1',$p->accessTokenHash());
        self::assertSame('nonce-1',$p->nonce());
    }
    public function testVerificationContextIsExplicit(): void
    {
        $c=new OAuthDPoPVerificationContext('GET','https://api.test/resource','token','nonce');
        self::assertSame('GET',$c->httpMethod());
        self::assertSame('token',$c->accessToken());
    }
    public function testVerifierReturnsTypedResult(): void
    {
        $m=new \ReflectionMethod(OAuthProofOfPossessionVerifierInterface::class,'verify');
        self::assertSame(OAuthDPoPVerificationResult::class,(string)$m->getReturnType());
    }
    public function testVerificationResultExposesKeyBinding(): void
    {
        $r=new OAuthDPoPVerificationResult('thumb-1','jti-1',new DateTimeImmutable('2026-08-09T10:00:00Z'));
        self::assertSame('thumb-1',$r->publicKeyThumbprint());
        self::assertSame('jti-1',$r->tokenId());
    }
    public function testReplayAndNonceAreContracts(): void
    {
        self::assertTrue((new \ReflectionClass(OAuthDPoPReplayStoreInterface::class))->isInterface());
        self::assertTrue((new \ReflectionClass(OAuthDPoPNonceServiceInterface::class))->isInterface());
    }
    public function testFoundationRemainsCryptoAndInfrastructureNeutral(): void
    {
        foreach ([OAuthProofOfPossessionVerifierInterface::class,OAuthDPoPReplayStoreInterface::class,OAuthDPoPNonceServiceInterface::class,OAuthDPoPProof::class] as $class) {
            $r=new \ReflectionClass($class);
            $s=file_get_contents((string)$r->getFileName());
            self::assertIsString($s);
            self::assertStringNotContainsString('PDO',$s);
            self::assertStringNotContainsString('Redis',$s);
            self::assertStringNotContainsString('openssl_',strtolower($s));
            self::assertStringNotContainsString('firebase',strtolower($s));
            self::assertStringNotContainsString('lcobucci',strtolower($s));
        }
    }
}
