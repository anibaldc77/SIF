<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlIdentityProviderMetadata;

interface SamlMetadataParserInterface
{
    public function parse(string $xml): SamlIdentityProviderMetadata;
}
