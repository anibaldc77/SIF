<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\Entitlement;
use Sif\Foundation\Security\Governance\EntitlementId;

interface EntitlementCatalogInterface
{
    public function find(EntitlementId $id): ?Entitlement;

    /**
     * @return list<Entitlement>
     */
    public function all(): array;
}
