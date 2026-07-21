<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Registry;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Exception\CommandRegistryFrozenException;
use Sif\Builder\Cli\Exception\DuplicateCommandException;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Sif\Builder\Tests\Cli\Fixtures\StubCommand;

final class CommandRegistryTest extends TestCase
{
    public function testPreservesRegistrationOrderAndNormalizesLookup(): void
    {
        $registry = new CommandRegistry();
        $build = new StubCommand('build');
        $validate = new StubCommand('validate');

        $registry->register($build);
        $registry->register($validate);

        self::assertSame([$build, $validate], $registry->all());
        self::assertSame($build, $registry->get(' BUILD '));
        self::assertTrue($registry->has('--validate'));
        self::assertSame(2, $registry->count());
    }

    public function testRejectsDuplicateNormalizedNames(): void
    {
        $registry = new CommandRegistry();
        $registry->register(new StubCommand('build'));

        $this->expectException(DuplicateCommandException::class);
        $registry->register(new StubCommand('BUILD'));
    }

    public function testFreezeIsIdempotentAndRejectsMutation(): void
    {
        $registry = new CommandRegistry();
        $registry->freeze();
        $registry->freeze();

        self::assertTrue($registry->isFrozen());

        $this->expectException(CommandRegistryFrozenException::class);
        $registry->register(new StubCommand('build'));
    }
}
