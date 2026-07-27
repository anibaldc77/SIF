<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum PersistenceCapability: string
{
    case Transactions = 'transactions';
    case NestedTransactions = 'nested_transactions';
    case Savepoints = 'savepoints';
    case ReadOnlyTransactions = 'read_only_transactions';
    case QueryCriteria = 'query_criteria';
    case Sorting = 'sorting';
    case OffsetPagination = 'offset_pagination';
    case Projection = 'projection';
    case StreamingResults = 'streaming_results';
    case OptimisticConcurrency = 'optimistic_concurrency';
    case UnitOfWork = 'unit_of_work';
}
