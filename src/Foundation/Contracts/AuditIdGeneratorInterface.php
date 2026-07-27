<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Audit\AuditId;

interface AuditIdGeneratorInterface
{
    public function generate(): AuditId;
}
