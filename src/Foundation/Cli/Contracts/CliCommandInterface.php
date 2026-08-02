<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Contracts;

use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliInvocation;

interface CliCommandInterface
{
    public function metadata(): CliCommandMetadata;

    public function execute(CliInvocation $invocation): CliCommandResult;
}
