<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Context;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextCarrier;
use Sif\Foundation\Context\ContextEnvelope;
use Sif\Foundation\Context\ContextEnvelopeFactory;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Contracts\ContextAwareInterface;
use Sif\Foundation\Contracts\ContextEnvelopeInterface;

final class ContextIntegrationContractsTest extends TestCase
{
    public function testEnvelopeImplementsExplicitIntegrationContracts(): void
    {
        $envelope = new ContextEnvelope(new \stdClass(), $this->context('ctx-001'));

        self::assertInstanceOf(ContextAwareInterface::class, $envelope);
        self::assertInstanceOf(ContextEnvelopeInterface::class, $envelope);
    }

    public function testEnvelopePreservesExactPayloadAndContextIdentity(): void
    {
        $payload = new \stdClass();
        $context = $this->context('ctx-001');
        $envelope = new ContextEnvelope($payload, $context);

        self::assertSame($payload, $envelope->payload());
        self::assertSame($context, $envelope->context());
    }

    public function testReplacingPayloadIsImmutableAndPreservesContext(): void
    {
        $originalPayload = new \stdClass();
        $replacementPayload = new \stdClass();
        $context = $this->context('ctx-001');
        $envelope = new ContextEnvelope($originalPayload, $context);

        self::assertSame($envelope, $envelope->withPayload($originalPayload));

        $replacement = $envelope->withPayload($replacementPayload);

        self::assertNotSame($envelope, $replacement);
        self::assertSame($originalPayload, $envelope->payload());
        self::assertSame($replacementPayload, $replacement->payload());
        self::assertSame($context, $replacement->context());
    }

    public function testReplacingContextIsImmutableAndPreservesPayload(): void
    {
        $payload = new \stdClass();
        $context = $this->context('ctx-001');
        $replacementContext = $this->context('ctx-002');
        $envelope = new ContextEnvelope($payload, $context);

        self::assertSame($envelope, $envelope->withContext($context));

        $replacement = $envelope->withContext($replacementContext);

        self::assertNotSame($envelope, $replacement);
        self::assertSame($context, $envelope->context());
        self::assertSame($replacementContext, $replacement->context());
        self::assertSame($payload, $replacement->payload());
    }

    public function testFactoryCreatesEnvelopeFromExplicitCarrier(): void
    {
        $payload = new \stdClass();
        $context = $this->context('ctx-001');
        $carrier = new ContextCarrier($context);

        $envelope = ContextEnvelopeFactory::fromCarrier($payload, $carrier);

        self::assertSame($payload, $envelope->payload());
        self::assertSame($context, $envelope->context());
    }

    public function testEnvelopeDoesNotRequirePayloadMutationOrSerialization(): void
    {
        $payload = new class () {
            public string $state = 'unchanged';
        };
        $envelope = new ContextEnvelope($payload, $this->context('ctx-001'));

        self::assertSame('unchanged', $payload->state);
        self::assertSame($payload, $envelope->payload());
        self::assertSame('unchanged', $payload->state);
    }

    private function context(string $id): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId($id),
            correlationId: new ContextId('correlation-001'),
            createdAt: new DateTimeImmutable('2026-07-27T17:00:00+00:00'),
            attributes: new ContextAttributes(['request_id' => 'req-001']),
            operation: 'integration.test',
            source: 'phpunit',
        );
    }
}
