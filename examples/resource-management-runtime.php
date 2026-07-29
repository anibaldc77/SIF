<?php

declare(strict_types=1);

use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Resources\Contribution\CompiledResourceContributionPlan;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRoot;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;
use Sif\Foundation\Resources\Registry\CompiledResourceRegistry;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

require dirname(__DIR__) . '/vendor/autoload.php';

$plan = new ResourceManagementPlan(
    new CompiledResourceRegistry([]),
    new CompiledResourceContributionPlan([], []),
    [new AuthorizedResourceRoot(
        new ResourceRootIdentifier('public'),
        dirname(__DIR__) . '/public',
    )],
);

$application = (new Bootstrap(resourceManagementPlan: $plan))
    ->createApplication(Environment::testing());

$application->boot();

$resolved = $application->resourcePathResolver()?->resolve(
    new ResourceRootIdentifier('public'),
    new ResourcePath('assets/app.css'),
);

var_dump($resolved?->absolutePath());
