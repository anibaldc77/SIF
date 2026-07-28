<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Logging\Normalization\BoundedStructuredValueNormalizer;
use Sif\Foundation\Logging\Normalization\NormalizationPolicy;
use Sif\Foundation\Logging\Normalization\NormalizedAttributes;
use Sif\Foundation\Logging\Redaction\RecursiveSecretRedactor;
use Sif\Foundation\Logging\Redaction\SecretRedactionPolicy;
use Sif\Foundation\Logging\Serialization\CanonicalStructuredValueSerializer;
use Stringable;

final class LoggingNormalizationTest extends TestCase
{
    public function testScalarsAndPortableObjectsAreNormalizedDeterministically(): void
    {
        $normalizer = new BoundedStructuredValueNormalizer();
        self::assertSame('2026-07-28T18:30:00.123456+00:00', $normalizer->normalize(new DateTimeImmutable('2026-07-28T18:30:00.123456Z')));
        self::assertSame('printable', $normalizer->normalize(new PrintableValue()));
        self::assertSame('[object:stdClass]', $normalizer->normalize(new \stdClass()));
    }

    public function testThrowableProjectionIsBoundedAndOmitsTrace(): void
    {
        $value = (new BoundedStructuredValueNormalizer())->normalize(new RuntimeException('failure', 42));
        self::assertSame(['type' => RuntimeException::class, 'message' => 'failure', 'code' => 42], $value);
        self::assertArrayNotHasKey('trace', $value);
    }

    public function testDepthIsBounded(): void
    {
        $normalizer = new BoundedStructuredValueNormalizer(new NormalizationPolicy(maxDepth: 1));
        self::assertSame(['a' => ['b' => '[maximum-depth-reached]']], $normalizer->normalize(['a' => ['b' => 'c']]));
    }

    public function testCollectionSizeIsBounded(): void
    {
        $normalizer = new BoundedStructuredValueNormalizer(new NormalizationPolicy(maxItemsPerCollection: 2));
        self::assertSame(['a' => 1, 'b' => 2, '__truncated__' => '[truncated]'], $normalizer->normalize(['a' => 1, 'b' => 2, 'c' => 3]));
    }

    public function testStringsAreTruncatedWithoutInspectingObjectState(): void
    {
        $normalizer = new BoundedStructuredValueNormalizer(new NormalizationPolicy(maxStringLength: 4));
        self::assertSame('abcd[truncated]', $normalizer->normalize('abcdef'));
    }

    public function testSensitiveKeysAreRedactedRecursivelyAndCaseInsensitively(): void
    {
        $redactor = new RecursiveSecretRedactor();
        self::assertSame(
            ['user' => 'ana', 'Authorization' => '[redacted]', 'nested' => ['api-key' => '[redacted]']],
            $redactor->redact(['user' => 'ana', 'Authorization' => 'Bearer secret', 'nested' => ['api-key' => '123']]),
        );
    }

    public function testCustomRedactionPolicyIsDeterministic(): void
    {
        $redactor = new RecursiveSecretRedactor(new SecretRedactionPolicy(['credential'], '<hidden>'));
        self::assertSame(['credential' => '<hidden>', 'token' => 'visible'], $redactor->redact(['credential' => 'x', 'token' => 'visible']));
    }

    public function testCanonicalSerializerSortsMapsAndPreservesListOrder(): void
    {
        $serializer = new CanonicalStructuredValueSerializer();
        self::assertSame('{"a":{"x":1,"y":2},"b":[3,2,1]}', $serializer->serialize(['b' => [3, 2, 1], 'a' => ['y' => 2, 'x' => 1]]));
    }

    public function testCanonicalSerializerPreservesFloatIdentity(): void
    {
        self::assertSame('{"value":1.0}', (new CanonicalStructuredValueSerializer())->serialize(['value' => 1.0]));
    }

    public function testNormalizedAttributesComposeNormalizationRedactionAndSerialization(): void
    {
        $attributes = NormalizedAttributes::fromRaw(
            ['z' => new PrintableValue(), 'password' => 'secret', 'a' => 1],
            new BoundedStructuredValueNormalizer(),
            new RecursiveSecretRedactor(),
        );
        self::assertSame(['z' => 'printable', 'password' => '[redacted]', 'a' => 1], $attributes->values());
        self::assertSame('{"a":1,"password":"[redacted]","z":"printable"}', $attributes->canonical(new CanonicalStructuredValueSerializer()));
    }
}

final class PrintableValue implements Stringable
{
    public function __toString(): string
    {
        return 'printable';
    }
}
