<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\BootResult;
use Sif\Foundation\ServiceProviderCollection;

interface ApplicationInterface
{
    public function runtime(): RuntimeInterface;
    public function kernel(): KernelInterface;
    public function environment(): EnvironmentInterface;
    public function providers(): ServiceProviderCollection;

    /** @return list<string> */
    public function capabilities(): array;

    public function hasCapability(string $capability): bool;

    public function addCapability(string $capability): void;

    public function boot(): BootResult;
    public function run(): BootResult;
    public function shutdown(): BootResult;
}
