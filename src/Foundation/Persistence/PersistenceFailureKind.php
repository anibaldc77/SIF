<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum PersistenceFailureKind: string
{
    case Connection = 'connection';
    case Transaction = 'transaction';
    case Query = 'query';
    case Mapping = 'mapping';
    case Concurrency = 'concurrency';
    case Repository = 'repository';
    case UnitOfWork = 'unit_of_work';
    case UnsupportedCapability = 'unsupported_capability';
    case Unknown = 'unknown';
}
