<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\QueryCriteria;
use Sif\Foundation\Persistence\SortOrder;

interface QueryInterface
{
    public function criteria(): QueryCriteria;

    public function sortOrder(): SortOrder;

    public function pagination(): ?Pagination;

    public function projection(): Projection;
}
