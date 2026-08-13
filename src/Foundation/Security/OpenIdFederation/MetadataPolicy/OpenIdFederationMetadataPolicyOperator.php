<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\MetadataPolicy;

enum OpenIdFederationMetadataPolicyOperator: string
{
    case Value = 'value';
    case Add = 'add';
    case Default = 'default';
    case OneOf = 'one_of';
    case SubsetOf = 'subset_of';
    case SupersetOf = 'superset_of';
    case Essential = 'essential';
}
