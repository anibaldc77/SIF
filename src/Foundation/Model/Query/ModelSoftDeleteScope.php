<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Query;

enum ModelSoftDeleteScope: string
{
    case WithoutTrashed = 'without_trashed';
    case WithTrashed = 'with_trashed';
    case OnlyTrashed = 'only_trashed';
}
