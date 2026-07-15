<?php
declare(strict_types=1);
namespace Sif\Foundation\Contracts;
interface BootstrapInterface { public function createApplication(EnvironmentInterface $environment): ApplicationInterface; }
