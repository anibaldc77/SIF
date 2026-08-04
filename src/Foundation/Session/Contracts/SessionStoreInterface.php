<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Contracts;

use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionRecord;

interface SessionStoreInterface
{
    public function read(SessionId $id): ?SessionRecord;

    public function write(SessionRecord $record): void;

    public function delete(SessionId $id): void;
}
