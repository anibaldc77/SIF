<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantId;

interface TrustedDeviceGrantIdGeneratorInterface
{
    public function generate(): TrustedDeviceGrantId;
}
