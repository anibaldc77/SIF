<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Audit\AuditSubject;

interface AuditableSubjectInterface
{
    public function auditSubject(): AuditSubject;
}
