<?php
declare(strict_types=1);
namespace Sif\Foundation\Contracts;
interface EnvironmentInterface { public function name(): string; public function isDevelopment(): bool; public function isTesting(): bool; public function isStaging(): bool; public function isProduction(): bool; public function equals(self $other): bool; public function __toString(): string; }
