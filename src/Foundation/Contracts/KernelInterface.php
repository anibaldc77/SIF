<?php
declare(strict_types=1);
namespace Sif\Foundation\Contracts;
use Sif\Foundation\BootResult;
interface KernelInterface { public function boot(ApplicationInterface $application): BootResult; public function run(ApplicationInterface $application): BootResult; public function shutdown(ApplicationInterface $application): BootResult; }
