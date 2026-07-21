<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Fixtures;

use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Contract\BuilderEngineInterface;

final class RecordingBuilderEngine implements BuilderEngineInterface
{
    public ?BuilderRequest $request = null;

    public function run(BuilderRequest $request): BuilderResult
    {
        $this->request = $request;

        return BuilderResult::succeeded([BuilderPhase::COMPLETED], runIdentifier: 'cli-test');
    }
}
