<?php
declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\BootstrapInterface;

final class Framework
{
    public static function create(
        ?Environment $environment = null,
        ?BootstrapInterface $bootstrap = null,
    ): ApplicationInterface {
        return ($bootstrap ?? new Bootstrap())->createApplication($environment ?? Environment::production());
    }

    public static function run(
        ?Environment $environment = null,
        ?BootstrapInterface $bootstrap = null,
    ): BootResult {
        return self::create($environment, $bootstrap)->run();
    }

    public static function version(): string { return '2.0.0-alpha1'; }
}
