<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Extension;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Exception\DuplicateExtensionException;
use Sif\Builder\Engine\Exception\ExtensionRegistryFrozenException;
use Sif\Builder\Engine\Extension\AnalysisResult;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;

final class AnalyzerRegistryTest extends TestCase
{
    public function testPreservesRegistrationOrderAndSupportsLookup(): void
    {
        $registry = new AnalyzerRegistry();
        $first = $this->analyzer('reference.broken');
        $second = $this->analyzer('reference.cycles');

        $registry->register($first);
        $registry->register($second);

        self::assertSame([$first, $second], $registry->all());
        self::assertTrue($registry->has('REFERENCE.BROKEN'));
        self::assertSame($second, $registry->get('reference.cycles'));
        self::assertNull($registry->get('repository.metadata'));
    }

    public function testRejectsDuplicateNormalizedIdentifier(): void
    {
        $registry = new AnalyzerRegistry();
        $registry->register($this->analyzer('reference.broken'));

        $this->expectException(DuplicateExtensionException::class);
        $registry->register($this->analyzer('  REFERENCE.BROKEN  '));
    }

    public function testSelectsRequestedAnalyzersInRequestOrderAndReportsMissingOnes(): void
    {
        $registry = new AnalyzerRegistry();
        $first = $this->analyzer('reference.broken');
        $second = $this->analyzer('reference.cycles');
        $registry->register($first);
        $registry->register($second);

        $selection = $registry->select([
            'reference.cycles',
            'repository.metadata',
            'reference.broken',
        ]);

        self::assertSame([$second, $first], $selection->analyzers);
        self::assertCount(1, $selection->diagnostics);
        self::assertSame('CONFIG-101', $selection->diagnostics->all()[0]->code);
        self::assertSame('repository.metadata', $selection->diagnostics->all()[0]->extension);
    }

    public function testEmptySelectionUsesAllRegisteredAnalyzers(): void
    {
        $registry = new AnalyzerRegistry();
        $first = $this->analyzer('reference.broken');
        $second = $this->analyzer('reference.cycles');
        $registry->register($first);
        $registry->register($second);

        $selection = $registry->select();

        self::assertSame([$first, $second], $selection->analyzers);
        self::assertTrue($selection->diagnostics->isEmpty());
    }

    public function testFreezeIsIdempotentAndPreventsFurtherRegistration(): void
    {
        $registry = new AnalyzerRegistry();
        $registry->freeze();
        $registry->freeze();

        self::assertTrue($registry->isFrozen());

        $this->expectException(ExtensionRegistryFrozenException::class);
        $registry->register($this->analyzer('reference.broken'));
    }

    private function analyzer(string $identifier): AnalyzerInterface
    {
        return new class ($identifier) implements AnalyzerInterface {
            public function __construct(private readonly string $identifier)
            {
            }

            public function id(): string
            {
                return $this->identifier;
            }

            public function analyze(BuilderContext $context): AnalysisResult
            {
                return new AnalysisResult();
            }
        };
    }
}
