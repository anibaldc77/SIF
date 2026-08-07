<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlResponse;

interface SamlResponseParserInterface
{
    public function parse(string $xml): SamlResponse;
}
