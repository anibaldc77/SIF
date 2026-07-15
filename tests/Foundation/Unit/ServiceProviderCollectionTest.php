<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Exceptions\DuplicateServiceProviderException;
use Sif\Foundation\Exceptions\ServiceProviderNotFoundException;
use Sif\Foundation\ServiceProviderCollection;
use Sif\Foundation\Tests\Fixtures\FirstRecordingProvider;
use Sif\Foundation\Tests\Fixtures\OperationLog;
use Sif\Foundation\Tests\Fixtures\SecondRecordingProvider;

final class ServiceProviderCollectionTest extends TestCase
{
    public function testNewCollectionIsEmpty(): void
    {
        $providers = new ServiceProviderCollection();

        self::assertTrue($providers->isEmpty());
        self::assertSame(0, $providers->count());
        self::assertSame([], $providers->all());
        self::assertSame([], iterator_to_array($providers));
    }

    public function testAddsAndGetsProviderByClass(): void
    {
        $providers = new ServiceProviderCollection();
        $provider = new FirstRecordingProvider(new OperationLog(), 'first');
        $providers->add($provider);

        self::assertFalse($providers->isEmpty());
        self::assertTrue($providers->has(FirstRecordingProvider::class));
        self::assertSame($provider, $providers->get(FirstRecordingProvider::class));
        self::assertSame(1, count($providers));
    }

    public function testRejectsDuplicateProviderClass(): void
    {
        $providers = new ServiceProviderCollection();
        $providers->add(new FirstRecordingProvider(new OperationLog(), 'first'));

        $this->expectException(DuplicateServiceProviderException::class);
        $providers->add(new FirstRecordingProvider(new OperationLog(), 'duplicate'));
    }

    public function testRejectsMissingProvider(): void
    {
        $this->expectException(ServiceProviderNotFoundException::class);
        (new ServiceProviderCollection())->get(FirstRecordingProvider::class);
    }

    public function testPreservesInsertionAndReverseOrder(): void
    {
        $providers = new ServiceProviderCollection();
        $first = new FirstRecordingProvider(new OperationLog(), 'first');
        $second = new SecondRecordingProvider(new OperationLog(), 'second');
        $providers->add($first);
        $providers->add($second);

        self::assertSame([$first, $second], $providers->all());
        self::assertSame([$second, $first], $providers->reverse());
    }
}
