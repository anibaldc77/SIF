<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\ScimResourceType;
use Sif\Foundation\Security\Scim\ScimSchemaDefinition;
use Sif\Foundation\Security\Scim\ScimServiceProviderConfig;

interface ScimDiscoveryProviderInterface
{
    public function serviceProviderConfig(): ScimServiceProviderConfig;

    /** @return list<ScimResourceType> */
    public function resourceTypes(): array;

    /** @return list<ScimSchemaDefinition> */
    public function schemas(): array;
}
