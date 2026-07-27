<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Audit;

use Sif\Foundation\Contracts\AuditEmitterInterface;
use Sif\Foundation\Contracts\AuditRecordInterface;

final class RecordingAuditEmitter implements AuditEmitterInterface
{
    /**
     * @var list<AuditRecordInterface>
     */
    private array $records = [];

    public function emit(AuditRecordInterface $record): AuditRecordInterface
    {
        $this->records[] = $record;

        return $record;
    }

    /**
     * @return list<AuditRecordInterface>
     */
    public function records(): array
    {
        return $this->records;
    }
}
