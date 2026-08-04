<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Contracts;

use Sif\Foundation\Session\SessionId;

interface SessionIdGeneratorInterface
{
    public function generate(): SessionId;
}
