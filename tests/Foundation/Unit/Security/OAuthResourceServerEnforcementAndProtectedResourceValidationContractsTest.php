<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAccessTokenResolverInterface;
use Sif\Foundation\Security\Contracts\OAuthProtectedResourceValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthResourceServerPolicyProviderInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceRequest;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceValidationResult;
use Sif\Foundation\Security\OAuth\Advanced\OAuthResourceServerPolicy;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstrainedAccessToken;

final class OAuthResourceServerEnforcementAndProtectedResourceValidationContractsTest extends TestCase
{
    public function testProtectedResourceRequestCarriesSecurityInputs(): void
    {
        $request = new OAuthProtectedResourceRequest('get', 'https://api.example.test/orders', 'token', 'proof');
        self::assertSame('GET', $request->httpMethod());
        self::assertSame('https://api.example.test/orders', $request->resourceUri());
        self::assertSame('token', $request->serializedAccessToken());
        self::assertSame('proof', $request->dpopProof());
    }

    public function testResourceServerPolicyCanRequireDpopAndSenderConstraint(): void
    {
        $policy = new OAuthResourceServerPolicy();
        self::assertTrue($policy->requireDpopProof());
        self::assertTrue($policy->requireSenderConstraint());
    }

    public function testValidationResultMakesSenderConstraintDecisionExplicit(): void
    {
        $result = new OAuthProtectedResourceValidationResult(true, 'user-1', 'client-1', true);
        self::assertTrue($result->authorized());
        self::assertSame('user-1', $result->subject());
        self::assertSame('client-1', $result->clientId());
        self::assertTrue($result->senderConstraintValidated());
    }

    public function testProtectedResourceValidatorIsTyped(): void
    {
        $method = new \ReflectionMethod(OAuthProtectedResourceValidatorInterface::class, 'validate');
        self::assertSame(OAuthProtectedResourceValidationResult::class, (string) $method->getReturnType());
    }

    public function testResourceServerDependenciesAreContractDriven(): void
    {
        $policy = new \ReflectionMethod(OAuthResourceServerPolicyProviderInterface::class, 'policyFor');
        $resolver = new \ReflectionMethod(OAuthAccessTokenResolverInterface::class, 'resolve');
        self::assertSame(OAuthResourceServerPolicy::class, (string) $policy->getReturnType());
        self::assertSame(OAuthSenderConstrainedAccessToken::class, (string) $resolver->getReturnType());
    }

    public function testResourceServerContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthProtectedResourceValidatorInterface::class,
            OAuthResourceServerPolicyProviderInterface::class,
            OAuthAccessTokenResolverInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());
            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
