<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Clock\FrozenFailureClock;
use Sif\Foundation\ErrorHandling\Factory\FailureEnvelopeFactory;
use Sif\Foundation\ErrorHandling\Factory\FixedFailureIdGenerator;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureId;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\Metadata\CompositeFailureMetadataEnricher;
use Sif\Foundation\ErrorHandling\Metadata\ExecutionContextFailureMetadataEnricher;
use Sif\Foundation\ErrorHandling\Metadata\SafeFailureMetadataNormalizer;
use Sif\Foundation\ErrorHandling\Metadata\SecretRedactingMetadataEnricher;
use Stringable;

final class SafeFailureMetadataTest extends TestCase
{
    public function testNormalizerPreservesStructuredValues(): void
    {
        $result = (new SafeFailureMetadataNormalizer())->normalize(['count' => 2, 'nested' => ['ok' => true]]);
        self::assertSame(['count' => 2, 'nested' => ['ok' => true]], $result);
    }

    public function testNormalizerTruncatesLongStrings(): void
    {
        $result = (new SafeFailureMetadataNormalizer(maximumStringLength: 12))->normalize(['value' => str_repeat('x', 20)]);
        self::assertSame('x[truncated]', $result['value']);
    }

    public function testNormalizerBoundsDepth(): void
    {
        $result = (new SafeFailureMetadataNormalizer(maximumDepth: 2))->normalize(['a' => ['b' => ['c' => 1]]]);
        self::assertIsArray($result['a']);
        self::assertIsArray($result['a']['b']);
        self::assertSame(['[truncated]'], $result['a']['b']);
    }

    public function testNormalizerProjectsThrowableWithoutTrace(): void
    {
        $result = (new SafeFailureMetadataNormalizer())->normalize(['error' => new RuntimeException('failed', 7)]);
        self::assertIsArray($result['error']);
        self::assertSame(RuntimeException::class, $result['error']['type']);
        self::assertArrayNotHasKey('trace', $result['error']);
    }

    public function testNormalizerConvertsStringableValues(): void
    {
        $value = new class implements Stringable { public function __toString(): string { return 'portable'; } };
        self::assertSame('portable', (new SafeFailureMetadataNormalizer())->normalize(['value' => $value])['value']);
    }

    public function testUnsupportedValuesAreReplaced(): void
    {
        $resource = fopen('php://memory', 'rb');
        self::assertIsResource($resource);
        self::assertSame('[unsupported]', (new SafeFailureMetadataNormalizer())->normalize(['value' => $resource])['value']);
        fclose($resource);
    }

    public function testSecretRedactionIsRecursiveAndCaseInsensitive(): void
    {
        $result = (new SecretRedactingMetadataEnricher())->enrich(['Password' => 'one', 'nested' => ['api_token' => 'two']]);
        self::assertSame('[redacted]', $result['Password']);
        self::assertSame('[redacted]', $result['nested']['api_token']);
    }

    public function testExecutionContextEnrichmentPreservesExistingScopeByDefault(): void
    {
        $enricher = new ExecutionContextFailureMetadataEnricher($this->context());
        self::assertSame(['manual' => true], $enricher->enrich(['execution_context' => ['manual' => true]])['execution_context']);
    }

    public function testExecutionContextCanIncludeCustomAttributes(): void
    {
        $result = (new ExecutionContextFailureMetadataEnricher($this->context(), includeCustomAttributes: true))->enrich([]);
        self::assertSame('ctx-1', $result['execution_context']['context_id']);
        self::assertSame(['request' => 'abc'], $result['execution_context']['attributes']);
    }

    public function testFactoryCreatesEnvelopeWithClassificationAndSafeMetadata(): void
    {
        $throwable = new RuntimeException('failure');
        $factory = new FailureEnvelopeFactory(
            new FixedFailureIdGenerator(new FailureId('failure-fixed')),
            new FrozenFailureClock(new DateTimeImmutable('2026-07-28T23:30:00+00:00')),
            new SafeFailureMetadataNormalizer(),
            new CompositeFailureMetadataEnricher([new SecretRedactingMetadataEnricher()]),
        );
        $classification = new ThrowableClassification(FailureCategory::application(), FailureSeverity::error(), FailureDisposition::permanent(), 'application.runtime');
        $envelope = $factory->create($throwable, $classification, new FailureOrigin('runtime'), ['token' => 'secret']);
        self::assertSame('failure-fixed', $envelope->id()->value());
        self::assertSame('[redacted]', $envelope->metadata()['token']);
        self::assertSame($throwable, $envelope->throwable());
    }

    private function context(): ExecutionContextInterface
    {
        return new class implements ExecutionContextInterface {
            public function contextId(): ContextId { return new ContextId('ctx-1'); }
            public function correlationId(): ContextId { return new ContextId('corr-1'); }
            public function causationId(): ?ContextId { return null; }
            public function parentContextId(): ?ContextId { return null; }
            public function actorId(): ?string { return 'actor-1'; }
            public function tenantId(): ?string { return 'tenant-1'; }
            public function operation(): ?string { return 'test'; }
            public function source(): ?string { return 'phpunit'; }
            public function locale(): ?string { return 'es-AR'; }
            public function timezone(): ?string { return 'America/Argentina/Mendoza'; }
            public function createdAt(): DateTimeImmutable { return new DateTimeImmutable('2026-07-28T20:00:00-03:00'); }
            public function attributes(): ContextAttributes { return new ContextAttributes(['request' => 'abc']); }
        };
    }
}
