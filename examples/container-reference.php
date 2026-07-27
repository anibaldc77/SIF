<?php

declare(strict_types=1);

use Sif\Foundation\Container\ContainerCompositionFactory;
use Sif\Foundation\Container\ScopeIdentifier;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Container\ServiceTag;

require dirname(__DIR__) . '/vendor/autoload.php';

final class ReferenceClock
{
    public function now(): string
    {
        return '2026-07-27T00:00:00+00:00';
    }
}

$composition = (new ContainerCompositionFactory())->create();

$composition->definitions()->register(
    ServiceDefinition::forClass(
        identifier: new ServiceIdentifier(ReferenceClock::class),
        className: ReferenceClock::class,
        lifetime: ServiceLifetime::Scoped,
        tags: [
            new ServiceTag(
                name: 'reference.service',
                priority: 100,
                metadata: ['role' => 'clock'],
            ),
        ],
    ),
);

$validation = $composition->validator()->validate();

if (!$validation->isValid()) {
    fwrite(STDERR, "Container validation failed.\n");
    exit(1);
}

$compiled = $composition->compiler()->compile();
$scope = $composition->container()->beginScope(
    new ScopeIdentifier('reference-example'),
);

$clock = $scope->get(
    new ServiceIdentifier(ReferenceClock::class),
);

if (!$clock instanceof ReferenceClock) {
    fwrite(STDERR, "Unexpected reference service.\n");
    exit(1);
}

$lazy = $scope->lazy(
    new ServiceIdentifier(ReferenceClock::class),
);

echo 'Validation diagnostics: ' . $validation->count() . PHP_EOL;
echo 'Compiled fingerprint: ' . $compiled->fingerprint() . PHP_EOL;
echo 'Tagged services: '
    . count($scope->tagged('reference.service'))
    . PHP_EOL;
echo 'Lazy resolved before access: '
    . ($lazy->isResolved() ? 'yes' : 'no')
    . PHP_EOL;
echo 'Clock: ' . $clock->now() . PHP_EOL;
echo 'Lazy shares scoped instance: '
    . ($lazy->resolve() === $clock ? 'yes' : 'no')
    . PHP_EOL;

$scope->close();
