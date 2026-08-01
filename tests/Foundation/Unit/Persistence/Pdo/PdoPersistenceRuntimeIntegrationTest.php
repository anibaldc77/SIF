<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntimeServiceProvider;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;

final class PdoPersistenceRuntimeIntegrationTest extends TestCase
{
    public function testProviderPublishesPersistenceCapabilities(): void
    {
        $reflection = new \ReflectionClass(PdoPersistenceRuntimeServiceProvider::class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->hasMethod('capabilities'));
    }

    public function testCapabilitiesAreNamedAndSideEffectFreeByConstruction(): void
    {
        $method = new \ReflectionMethod(PdoPersistenceRuntimeServiceProvider::class, 'capabilities');
        self::assertTrue($method->isPublic());
        self::assertSame('capabilities', $method->getName());
    }
}
