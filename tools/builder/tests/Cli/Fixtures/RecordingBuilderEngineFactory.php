<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Fixtures;

use Sif\Builder\Cli\Contract\BuilderEngineFactoryInterface;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\Contract\BuilderEngineInterface;

final class RecordingBuilderEngineFactory implements BuilderEngineFactoryInterface
{
    /** @var list<EngineExecutionMode> */
    public array $modes = [];

    public function __construct(public readonly RecordingBuilderEngine $engine = new RecordingBuilderEngine())
    {
    }

    public function create(EngineExecutionMode $mode): BuilderEngineInterface
    {
        $this->modes[] = $mode;

        return $this->engine;
    }
}
