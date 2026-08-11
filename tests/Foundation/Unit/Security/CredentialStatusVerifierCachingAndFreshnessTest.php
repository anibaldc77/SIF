<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialStatusCacheInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusRefreshPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusResolutionFailurePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatus;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusResolutionDecision;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusResolutionFailureMode;

final class CredentialStatusVerifierCachingAndFreshnessTest extends TestCase
{
    private function statusEvidence(): CredentialStatusEvidence
    {
        return new CredentialStatusEvidence(
            new CredentialStatus(CredentialStatus::VALID),
            new DateTimeImmutable('2026-08-11T12:00:00Z'),
            'status-list'
        );
    }

    public function testCacheEntryDistinguishesFreshAndUsableStaleWindows(): void
    {
        $stored = new DateTimeImmutable('2026-08-11T12:00:00Z');
        $fresh = new DateTimeImmutable('2026-08-11T12:05:00Z');
        $stale = new DateTimeImmutable('2026-08-11T12:10:00Z');

        $entry = new CredentialStatusCacheEntry(
            'credential-status:key',
            $this->statusEvidence(),
            $stored,
            $fresh,
            $stale
        );

        self::assertTrue($entry->isFreshAt(
            new DateTimeImmutable('2026-08-11T12:04:59Z')
        ));
        self::assertFalse($entry->isUsableStaleAt(
            new DateTimeImmutable('2026-08-11T12:04:59Z')
        ));
        self::assertTrue($entry->isUsableStaleAt(
            new DateTimeImmutable('2026-08-11T12:07:00Z')
        ));
        self::assertFalse($entry->isUsableStaleAt(
            new DateTimeImmutable('2026-08-11T12:11:00Z')
        ));
    }

    public function testResolutionDecisionMakesCacheStalenessAndRefreshExplicit(): void
    {
        $decision = new CredentialStatusResolutionDecision(
            $this->statusEvidence(),
            true,
            true,
            true
        );

        self::assertTrue($decision->fromCache());
        self::assertTrue($decision->stale());
        self::assertTrue($decision->refreshRecommended());
        self::assertSame('valid', $decision->result()->status()->value());
    }

    public function testFailureModesAreExplicit(): void
    {
        self::assertSame(
            'fail_closed',
            CredentialStatusResolutionFailureMode::FailClosed->value
        );
        self::assertSame(
            'allow_usable_stale',
            CredentialStatusResolutionFailureMode::AllowUsableStale->value
        );
    }

    public function testVerifierCacheAndPoliciesAreSeparatedContracts(): void
    {
        foreach ([
            CredentialStatusCacheInterface::class,
            CredentialStatusRefreshPolicyInterface::class,
            CredentialStatusResolutionFailurePolicyInterface::class,
        ] as $contract) {
            self::assertTrue((new \ReflectionClass($contract))->isInterface());
        }
    }

    public function testCacheContractUsesTypedEntry(): void
    {
        $get = new \ReflectionMethod(CredentialStatusCacheInterface::class, 'get');
        $put = new \ReflectionMethod(CredentialStatusCacheInterface::class, 'put');

        self::assertSame(
            '?' . CredentialStatusCacheEntry::class,
            (string) $get->getReturnType()
        );
        self::assertSame(
            CredentialStatusCacheEntry::class,
            (string) $put->getParameters()[0]->getType()
        );
    }

    public function testVerifierCachingLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            CredentialStatusCacheInterface::class,
            CredentialStatusRefreshPolicyInterface::class,
            CredentialStatusResolutionFailurePolicyInterface::class,
            CredentialStatusCacheEntry::class,
            CredentialStatusResolutionDecision::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('sleep(', strtolower($source));
        }
    }
}


