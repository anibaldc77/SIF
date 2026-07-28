<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Exceptions\DuplicateModuleException;
use Sif\Foundation\Modules\Exceptions\FrozenModuleRegistryException;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;

final class ModuleRegistryTest extends TestCase
{
    public function testItStartsEmptyAndMutable(): void
    {
        $registry = new ModuleRegistry();

        self::assertFalse($registry->isFrozen());
        self::assertSame([], $registry->descriptors());
        self::assertFalse($registry->has(new ModuleId('sif.missing')));
        self::assertNull($registry->descriptor(new ModuleId('sif.missing')));
    }

    public function testItRegistersAndRetrievesModuleDescriptor(): void
    {
        $registry = new ModuleRegistry();
        $module = $this->module('sif.alpha', 'Alpha');

        $registry->register($module);

        self::assertTrue($registry->has(new ModuleId('sif.alpha')));
        self::assertSame($module->descriptor(), $registry->descriptor(new ModuleId('sif.alpha')));
    }

    public function testDescriptorsPreserveRegistrationOrder(): void
    {
        $registry = new ModuleRegistry();
        $alpha = $this->module('sif.alpha', 'Alpha');
        $beta = $this->module('sif.beta', 'Beta');

        $registry->register($alpha);
        $registry->register($beta);

        self::assertSame([$alpha->descriptor(), $beta->descriptor()], $registry->descriptors());
    }

    public function testDuplicateModuleIdIsRejectedEvenWhenDescriptorDiffers(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('sif.alpha', 'Alpha'));

        $this->expectException(DuplicateModuleException::class);
        $this->expectExceptionMessage('Module "sif.alpha" is already registered.');

        $registry->register($this->module('sif.alpha', 'Another Alpha'));
    }

    public function testFreezeIsIdempotentAndObservable(): void
    {
        $registry = new ModuleRegistry();

        $registry->freeze();
        $registry->freeze();

        self::assertTrue($registry->isFrozen());
    }

    public function testFrozenRegistryRejectsRegistrationWithoutChangingState(): void
    {
        $registry = new ModuleRegistry();
        $alpha = $this->module('sif.alpha', 'Alpha');
        $registry->register($alpha);
        $registry->freeze();

        try {
            $registry->register($this->module('sif.beta', 'Beta'));
            self::fail('Expected frozen registry mutation to fail.');
        } catch (FrozenModuleRegistryException $exception) {
            self::assertSame('Frozen module registry cannot be modified.', $exception->getMessage());
        }

        self::assertSame([$alpha->descriptor()], $registry->descriptors());
    }

    private function module(string $id, string $name): ModuleInterface
    {
        return new class ($id, $name) implements ModuleInterface {
            private ModuleDescriptor $descriptor;

            public function __construct(string $id, string $name)
            {
                $this->descriptor = new ModuleDescriptor(
                    new ModuleId($id),
                    new ModuleVersion('1.0.0'),
                    $name,
                );
            }

            public function descriptor(): ModuleDescriptor
            {
                return $this->descriptor;
            }
        };
    }
}
