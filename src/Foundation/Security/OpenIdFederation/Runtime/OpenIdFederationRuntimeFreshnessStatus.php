<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Runtime;

enum OpenIdFederationRuntimeFreshnessStatus: string
{
    case Fresh = 'fresh';
    case StaleUsable = 'stale_usable';
    case Expired = 'expired';
}
