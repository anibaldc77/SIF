<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Attribute;

enum AuthorizationAttributeScope: string
{
    case Subject = 'subject';
    case Resource = 'resource';
    case Environment = 'environment';
}
