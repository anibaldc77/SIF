<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Exceptions\CredentialStatusResolutionUnavailableException;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatus;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusCacheEntry;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\CredentialStatusResolutionFailureMode;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\DefaultCredentialStatusResolutionFailurePolicy;

final class CredentialStatusResolutionFailurePolicyAndResilienceTest extends TestCase
{
    private function cacheEntry(): CredentialStatusCacheEntry
    {
        return new CredentialStatusCacheEntry(
            'credential-status:key',
            new CredentialStatusEvidence(
                new CredentialStatus(CredentialStatus::VALID),
                new DateTimeImmutable('2026-08-11T12:00:00Z'),
                'status-list'
            ),
            new DateTimeImmutable('2026-08-11T12:00:00Z'),
            new DateTimeImmutable('2026-08-11T12:05:00Z'),
            new DateTimeImmutable('2026-08-11T12:10:00Z')
        );
    }

    public function testFailClosedIsTheDefaultFailureMode(): void
    {
        $policy = new DefaultCredentialStatusResolutionFailurePolicy();

        $this->expectException(
            CredentialStatusResolutionUnavailableException::class
        );

        $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-11T12:07:00Z'),
            new RuntimeException('Resolver unavailable.')
        );
    }

    public function testAllowUsableStaleReturnsCachedEvidenceWithinStaleWindow(): void
    {
        $policy = new DefaultCredentialStatusResolutionFailurePolicy(
            CredentialStatusResolutionFailureMode::AllowUsableStale
        );

        $decision = $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-11T12:07:00Z'),
            new RuntimeException('Resolver unavailable.')
        );

        self::assertTrue($decision->fromCache());
        self::assertTrue($decision->stale());
        self::assertTrue($decision->refreshRecommended());
        self::assertSame('valid', $decision->result()->status()->value());
    }

    public function testAllowUsableStaleRejectsExpiredCachedEvidence(): void
    {
        $policy = new DefaultCredentialStatusResolutionFailurePolicy(
            CredentialStatusResolutionFailureMode::AllowUsableStale
        );

        $this->expectException(
            CredentialStatusResolutionUnavailableException::class
        );

        $policy->decide(
            $this->cacheEntry(),
            new DateTimeImmutable('2026-08-11T12:10:01Z'),
            new RuntimeException('Resolver unavailable.')
        );
    }

    public function testAllowUsableStaleRejectsMissingCacheEntry(): void
    {
        $policy = new DefaultCredentialStatusResolutionFailurePolicy(
            CredentialStatusResolutionFailureMode::AllowUsableStale
        );

        $this->expectException(
            CredentialStatusResolutionUnavailableException::class
        );

        $policy->decide(
            null,
            new DateTimeImmutable('2026-08-11T12:07:00Z'),
            new RuntimeException('Resolver unavailable.')
        );
    }

    public function testFailureExceptionPreservesOriginalCause(): void
    {
        $policy = new DefaultCredentialStatusResolutionFailurePolicy();
        $failure = new RuntimeException('Network failure.');

        try {
            $policy->decide(
                null,
                new DateTimeImmutable('2026-08-11T12:07:00Z'),
                $failure
            );

            self::fail('Expected resolution failure was not raised.');
        } catch (CredentialStatusResolutionUnavailableException $exception) {
            self::assertSame($failure, $exception->getPrevious());
        }
    }

    public function testFailurePolicyRemainsInfrastructureNeutral(): void
    {
        $reflection = new \ReflectionClass(
            DefaultCredentialStatusResolutionFailurePolicy::class
        );

        $source = file_get_contents((string) $reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Guzzle', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
        self::assertStringNotContainsString('sleep(', strtolower($source));
    }
}