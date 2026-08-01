<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Relation;

enum ModelRelationType: string
{
    case BelongsTo = 'belongs_to';
    case HasOne = 'has_one';
    case HasMany = 'has_many';
}
