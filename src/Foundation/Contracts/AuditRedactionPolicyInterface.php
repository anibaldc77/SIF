<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface AuditRedactionPolicyInterface
{
    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    public function redact(array $values): array;
}
