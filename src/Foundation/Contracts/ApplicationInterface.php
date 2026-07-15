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
    public function boot(): BootResult;
    public function run(): BootResult;
    public function shutdown(): BootResult;
}
