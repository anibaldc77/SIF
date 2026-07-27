<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ConstructorArgumentBindings;
use Sif\Foundation\Container\ConstructorBindingKind;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Exceptions\InvalidConstructorBindingException;

final class ConstructorArgumentBindingsTest extends TestCase
{
    public function testBindingsAreDeterministicallyOrdered(): void
    {
        $bindings = new ConstructorArgumentBindings([
            'zeta' => ConstructorArgumentBinding::value(1),
            'alpha' => ConstructorArgumentBinding::value(2),
        ]);

        self::assertSame(
            ['alpha', 'zeta'],
            array_keys($bindings->all()),
        );
    }

    public function testValueBindingPreservesExplicitValue(): void
    {
        $binding = ConstructorArgumentBinding::value('example');

        self::assertSame(ConstructorBindingKind::Value, $binding->kind());
        self::assertSame('example', $binding->boundValue());
        self::assertNull($binding->serviceIdentifier());
    }

    public function testServiceBindingPreservesIdentifier(): void
    {
        $identifier = new ServiceIdentifier('dependency');
        $binding = ConstructorArgumentBinding::service($identifier);

        self::assertSame(ConstructorBindingKind::Service, $binding->kind());
        self::assertSame($identifier, $binding->serviceIdentifier());
    }

    public function testEmptyParameterNameIsRejected(): void
    {
        $this->expectException(InvalidConstructorBindingException::class);

        new ConstructorArgumentBindings([
            ' ' => ConstructorArgumentBinding::value('invalid'),
        ]);
    }

    public function testUnknownBindingFailsPredictably(): void
    {
        $this->expectException(InvalidConstructorBindingException::class);

        (new ConstructorArgumentBindings())->get('missing');
    }
}
