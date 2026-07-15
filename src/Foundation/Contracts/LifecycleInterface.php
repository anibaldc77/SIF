<?php
declare(strict_types=1);
namespace Sif\Foundation\Contracts;
use Sif\Foundation\BootResult;
interface LifecycleInterface { /** @return list<\Sif\Foundation\BootStage> */ public function bootStages(): array; /** @return list<\Sif\Foundation\BootStage> */ public function shutdownStages(): array; public function boot(ApplicationInterface $application): BootResult; public function shutdown(ApplicationInterface $application): BootResult; }
