<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Authorization\AuthorizationAction;
use Sif\Foundation\Security\Authorization\AuthorizationContext;
use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\AuthorizationFailureReason;
use Sif\Foundation\Security\Authorization\AuthorizationManager;
use Sif\Foundation\Security\Authorization\AuthorizationPolicyId;
use Sif\Foundation\Security\Authorization\AuthorizationPolicyRegistry;
use Sif\Foundation\Security\Authorization\AuthorizationRequest;
use Sif\Foundation\Security\Authorization\AuthorizationResource;
use Sif\Foundation\Security\Contracts\AuthorizationPolicyInterface;
use Sif\Foundation\Security\Exceptions\DuplicateAuthorizationPolicyException;
use Sif\Foundation\Security\Identity\AnonymousPrincipal;

final class AuthorizationPoliciesAndDecisionEngineTest extends TestCase
{
    public function testRequestUsesDeterministicValueObjects(): void
    {
        $request = new AuthorizationRequest(
            new AnonymousPrincipal(),
            new AuthorizationAction('invoice.read'),
            new AuthorizationResource('invoice', '42', ['tenant' => 'acme', 'owner' => '7']),
            new AuthorizationContext(['channel' => 'http'])
        );

        self::assertSame('invoice.read', $request->action()->value());
        self::assertSame(['owner' => '7', 'tenant' => 'acme'], $request->resource()->attributes());
        self::assertSame(['channel' => 'http'], $request->context()->attributes());
    }

    public function testRegistryRejectsDuplicatePolicyIdentifiers(): void
    {
        $registry = new AuthorizationPolicyRegistry();
        $registry->register($this->policy('invoice.read', true));

        $this->expectException(DuplicateAuthorizationPolicyException::class);
        $registry->register($this->policy('invoice.read', false));
    }

    public function testManagerFailsClosedWhenNoPolicyApplies(): void
    {
        $decision = (new AuthorizationManager(new AuthorizationPolicyRegistry()))->decide($this->request());

        self::assertFalse($decision->isAllowed());
        self::assertSame(AuthorizationFailureReason::NO_APPLICABLE_POLICY, $decision->reason());
    }

    public function testAnyApplicableDenialOverridesAllows(): void
    {
        $registry = new AuthorizationPolicyRegistry();
        $registry->register($this->policy('allow', true));
        $registry->register($this->policy('deny', false));

        $decision = (new AuthorizationManager($registry))->decide($this->request());

        self::assertFalse($decision->isAllowed());
        self::assertSame(AuthorizationFailureReason::NOT_AUTHORIZED, $decision->reason());
    }

    public function testAllApplicablePoliciesMustAllow(): void
    {
        $registry = new AuthorizationPolicyRegistry();
        $registry->register($this->policy('first', true));
        $registry->register($this->policy('second', true));

        self::assertTrue((new AuthorizationManager($registry))->decide($this->request())->isAllowed());
    }

    public function testTechnicalFailureIsConvertedIntoClosedDecision(): void
    {
        $registry = new AuthorizationPolicyRegistry();
        $registry->register(new class implements AuthorizationPolicyInterface {
            public function id(): AuthorizationPolicyId { return new AuthorizationPolicyId('broken'); }
            public function supports(AuthorizationRequest $request): bool { return true; }
            public function decide(AuthorizationRequest $request): AuthorizationDecision { throw new RuntimeException('secret detail'); }
        });

        $decision = (new AuthorizationManager($registry))->decide($this->request());

        self::assertFalse($decision->isAllowed());
        self::assertSame(AuthorizationFailureReason::TECHNICAL_FAILURE, $decision->reason());
    }

    private function request(): AuthorizationRequest
    {
        return new AuthorizationRequest(
            new AnonymousPrincipal(),
            new AuthorizationAction('document.read'),
            new AuthorizationResource('document', '1')
        );
    }

    private function policy(string $id, bool $allow): AuthorizationPolicyInterface
    {
        return new class ($id, $allow) implements AuthorizationPolicyInterface {
            public function __construct(private string $idValue, private bool $allow) {}
            public function id(): AuthorizationPolicyId { return new AuthorizationPolicyId($this->idValue); }
            public function supports(AuthorizationRequest $request): bool { return true; }
            public function decide(AuthorizationRequest $request): AuthorizationDecision
            {
                return $this->allow ? AuthorizationDecision::allow() : AuthorizationDecision::deny();
            }
        };
    }
}
