<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlRequestId;

interface SamlRequestIdGeneratorInterface
{
    public function generate(): SamlRequestId;
}
