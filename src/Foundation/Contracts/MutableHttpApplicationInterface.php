<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Http\Runtime\HttpRuntime;

interface MutableHttpApplicationInterface extends HttpAwareApplicationInterface
{
    public function setHttp(HttpRuntime $http): void;
}
