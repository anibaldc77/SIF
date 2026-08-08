<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimPreconditionEvaluatorInterface;
use Sif\Foundation\Security\Exceptions\ScimPreconditionFailedException;
use Sif\Foundation\Security\Scim\Versioning\DefaultScimPreconditionEvaluator;
use Sif\Foundation\Security\Scim\Versioning\ScimEntityTag;
use Sif\Foundation\Security\Scim\Versioning\ScimPrecondition;
use Sif\Foundation\Security\Scim\Versioning\ScimResourceVersion;
use Sif\Foundation\Security\Scim\Versioning\ScimVersionGuard;

final class ScimResourceVersioningETagPreconditionsAndConcurrencyControlTest extends TestCase
{
    public function testResourceVersionProducesWeakEtag(): void
    {
        $version = new ScimResourceVersion('v42');

        self::assertSame('v42', $version->value());
        self::assertSame('W/"v42"', $version->weakEtag());
    }

    public function testIfMatchSucceedsWhenVersionMatches(): void
    {
        $result = (new DefaultScimPreconditionEvaluator())->evaluate(
            new ScimPrecondition(
                ScimPrecondition::IF_MATCH,
                [new ScimEntityTag('v42')]
            ),
            new ScimResourceVersion('v42')
        );

        self::assertTrue($result->satisfied());
        self::assertNull($result->reason());
    }

    public function testIfMatchFailsWhenVersionDiffers(): void
    {
        $result = (new DefaultScimPreconditionEvaluator())->evaluate(
            new ScimPrecondition(
                ScimPrecondition::IF_MATCH,
                [new ScimEntityTag('v41')]
            ),
            new ScimResourceVersion('v42')
        );

        self::assertFalse($result->satisfied());
        self::assertSame(
            'version_mismatch',
            $result->reason()
        );
    }

    public function testIfNoneMatchWildcardRequiresMissingResource(): void
    {
        $evaluator = new DefaultScimPreconditionEvaluator();

        $missing = $evaluator->evaluate(
            new ScimPrecondition(
                ScimPrecondition::IF_NONE_MATCH,
                [],
                true
            ),
            null
        );

        $existing = $evaluator->evaluate(
            new ScimPrecondition(
                ScimPrecondition::IF_NONE_MATCH,
                [],
                true
            ),
            new ScimResourceVersion('v1')
        );

        self::assertTrue($missing->satisfied());
        self::assertFalse($existing->satisfied());
        self::assertSame(
            'resource_exists',
            $existing->reason()
        );
    }

    public function testVersionGuardThrowsOnFailedPrecondition(): void
    {
        $guard = new ScimVersionGuard(
            new DefaultScimPreconditionEvaluator()
        );

        $this->expectException(
            ScimPreconditionFailedException::class
        );

        $guard->assertSatisfied(
            new ScimPrecondition(
                ScimPrecondition::IF_MATCH,
                [new ScimEntityTag('expected')]
            ),
            new ScimResourceVersion('actual')
        );
    }

    public function testVersioningContractsRemainStorageAndTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            ScimPreconditionEvaluatorInterface::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('SQL', strtoupper($source));
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('Okta', $source);
    }
}
