<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Extension;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Exception\DuplicateExtensionException;
use Sif\Builder\Engine\Exception\ExtensionRegistryFrozenException;
use Sif\Builder\Engine\Extension\GenerationResult;
use Sif\Builder\Engine\Extension\GeneratorRegistry;

final class GeneratorRegistryTest extends TestCase
{
    public function testPreservesRegistrationOrderAndSupportsLookup(): void
    {
        $registry = new GeneratorRegistry();
        $first = $this->generator('repository.index');
        $second = $this->generator('reference.report');

        $registry->register($first);
        $registry->register($second);

        self::assertSame([$first, $second], $registry->all());
        self::assertTrue($registry->has('REPOSITORY.INDEX'));
        self::assertSame($second, $registry->get('reference.report'));
        self::assertNull($registry->get('repository.graph'));
    }

    public function testRejectsDuplicateNormalizedIdentifier(): void
    {
        $registry = new GeneratorRegistry();
        $registry->register($this->generator('repository.index'));

        $this->expectException(DuplicateExtensionException::class);
        $registry->register($this->generator(' REPOSITORY.INDEX '));
    }

    public function testSelectsRequestedGeneratorsAndReportsMissingOnes(): void
    {
        $registry = new GeneratorRegistry();
        $first = $this->generator('repository.index');
        $second = $this->generator('reference.report');
        $registry->register($first);
        $registry->register($second);

        $selection = $registry->select([
            'reference.report',
            'repository.graph',
            'repository.index',
        ]);

        self::assertSame([$second, $first], $selection->generators);
        self::assertCount(1, $selection->diagnostics);
        self::assertSame('CONFIG-102', $selection->diagnostics->all()[0]->code);
        self::assertSame('repository.graph', $selection->diagnostics->all()[0]->extension);
    }

    public function testEmptySelectionUsesAllRegisteredGenerators(): void
    {
        $registry = new GeneratorRegistry();
        $first = $this->generator('repository.index');
        $second = $this->generator('reference.report');
        $registry->register($first);
        $registry->register($second);

        $selection = $registry->select();

        self::assertSame([$first, $second], $selection->generators);
        self::assertTrue($selection->diagnostics->isEmpty());
    }

    public function testFreezePreventsFurtherRegistrationButKeepsReadsAvailable(): void
    {
        $registry = new GeneratorRegistry();
        $generator = $this->generator('repository.index');
        $registry->register($generator);
        $registry->freeze();

        self::assertSame($generator, $registry->get('repository.index'));

        $this->expectException(ExtensionRegistryFrozenException::class);
        $registry->register($this->generator('reference.report'));
    }

    private function generator(string $identifier): GeneratorInterface
    {
        return new class ($identifier) implements GeneratorInterface {
            public function __construct(private readonly string $identifier)
            {
            }

            public function id(): string
            {
                return $this->identifier;
            }

            public function generate(BuilderContext $context): GenerationResult
            {
                return new GenerationResult();
            }
        };
    }
}
