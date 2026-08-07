<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlAssertion;

interface SamlAssertionParserInterface
{
    public function parse(string $xml): SamlAssertion;
}
