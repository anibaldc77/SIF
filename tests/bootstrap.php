<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Sif\\Foundation\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Foundation'.DIRECTORY_SEPARATOR.$relativePath.'.php';

    if (is_file($path)) {
        require $path;
    }
});
