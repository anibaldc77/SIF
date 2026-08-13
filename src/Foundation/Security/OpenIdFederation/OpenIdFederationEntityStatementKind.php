<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation;

enum OpenIdFederationEntityStatementKind: string
{
    case EntityConfiguration = 'entity_configuration';
    case SubordinateStatement = 'subordinate_statement';
}
