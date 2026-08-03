<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Http\Runtime\HttpRuntime;

interface HttpAwareApplicationInterface extends ApplicationInterface
{
    public function http(): ?HttpRuntime;
}
