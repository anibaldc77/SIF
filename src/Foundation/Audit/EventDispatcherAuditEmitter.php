<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditEmitterInterface;
use Sif\Foundation\Contracts\AuditRecordInterface;
use Sif\Foundation\Contracts\EventDispatcherInterface;

final readonly class EventDispatcherAuditEmitter implements AuditEmitterInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function emit(AuditRecordInterface $record): AuditRecordInterface
    {
        $this->dispatcher->dispatch(
            new AuditRecordCreated($record),
        );

        return $record;
    }
}
