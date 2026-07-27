<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface AuditSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(AuditRecordInterface $record): array;
}
