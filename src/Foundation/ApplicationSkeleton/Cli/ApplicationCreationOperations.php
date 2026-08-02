<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Cli;

use Closure;
use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationExecutor;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationPlan;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationPlanner;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\Cli\Value\CliInvocation;

final readonly class ApplicationCreationOperations
{
    /** @var Closure(CliInvocation): ProjectManifest */
    private Closure $manifestFactory;
    /** @var Closure(string): SkeletonFilesystemInterface */
    private Closure $filesystemFactory;
    /** @var Closure(SkeletonGenerationPlan, CliInvocation): ?ApplicationCreationAuthorization */
    private Closure $authorizationProvider;

    /**
     * @param callable(CliInvocation): ProjectManifest $manifestFactory
     * @param callable(string): SkeletonFilesystemInterface $filesystemFactory
     * @param callable(SkeletonGenerationPlan, CliInvocation): ?ApplicationCreationAuthorization $authorizationProvider
     */
    public function __construct(
        callable $manifestFactory,
        callable $filesystemFactory,
        callable $authorizationProvider,
        private ApplicationTemplateBlueprintFactory $blueprintFactory = new ApplicationTemplateBlueprintFactory(),
    ) {
        $this->manifestFactory = Closure::fromCallable($manifestFactory);
        $this->filesystemFactory = Closure::fromCallable($filesystemFactory);
        $this->authorizationProvider = Closure::fromCallable($authorizationProvider);
    }

    /** @return array{plan: SkeletonGenerationPlan, executed: bool, authorized: bool} */
    public function create(CliInvocation $invocation): array
    {
        $target = $invocation->argument(0) ?? '';
        $manifest = ($this->manifestFactory)($invocation);
        $filesystem = ($this->filesystemFactory)($target);
        $plan = (new SkeletonGenerationPlanner($filesystem))->plan($this->blueprintFactory->create($manifest));

        if (!$invocation->hasOption('execute')) {
            return ['plan' => $plan, 'executed' => false, 'authorized' => false];
        }

        $authorization = ($this->authorizationProvider)($plan, $invocation);
        if ($authorization === null || !$authorization->authorizes($plan->fingerprint())) {
            return ['plan' => $plan, 'executed' => false, 'authorized' => false];
        }

        (new SkeletonGenerationExecutor($filesystem))->execute($plan);

        return ['plan' => $plan, 'executed' => true, 'authorized' => true];
    }
}
