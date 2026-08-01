<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

enum ModelLifecycleOperation: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case SoftDelete = 'soft_delete';
    case Restore = 'restore';
}
