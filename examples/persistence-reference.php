<?php

declare(strict_types=1);

use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\SortField;
use Sif\Foundation\Persistence\StorageRecord;
use Sif\Foundation\Persistence\Memory\InMemoryConnection;
use Sif\Foundation\Persistence\Memory\InMemoryQueryEvaluator;
use Sif\Foundation\Persistence\Memory\InMemoryRepository;
use Sif\Foundation\Persistence\Memory\InMemoryStorage;
use Sif\Foundation\Persistence\Memory\InMemoryTransactionManager;

require dirname(__DIR__) . '/vendor/autoload.php';

final readonly class ReferenceDocument
{
    public function __construct(
        public int $id,
        public string $title,
        public string $status,
    ) {
    }
}

/**
 * @implements MapperInterface<ReferenceDocument>
 */
final readonly class ReferenceDocumentMapper implements MapperInterface
{
    public function hydrate(StorageRecord $record): object
    {
        return new ReferenceDocument(
            id: (int) $record->get('id'),
            title: (string) $record->get('title'),
            status: (string) $record->get('status'),
        );
    }

    public function extract(object $object): StorageRecord
    {
        return new StorageRecord([
            'id' => $object->id,
            'title' => $object->title,
            'status' => $object->status,
        ]);
    }
}

$connection = new InMemoryConnection(
    new ConnectionName('memory'),
);

$transactionManager = new InMemoryTransactionManager();
$storage = new InMemoryStorage();

$repository = new InMemoryRepository(
    name: new RepositoryName('documents'),
    managedType: ReferenceDocument::class,
    collection: 'documents',
    mapper: new ReferenceDocumentMapper(),
    storage: $storage,
    queryEvaluator: new InMemoryQueryEvaluator(),
    identifierResolver: static fn (
        ReferenceDocument $document,
    ): int => $document->id,
);

$transactionManager->transactional(
    static function () use ($repository): void {
        $repository->save(
            new ReferenceDocument(1, 'Budget Report', 'draft'),
        );
        $repository->save(
            new ReferenceDocument(2, 'Legal Opinion', 'approved'),
        );
        $repository->save(
            new ReferenceDocument(3, 'Technical Report', 'approved'),
        );
    },
);

$query = (new Query())
    ->withCriterion(
        new QueryCriterion(
            'status',
            QueryOperator::Equal,
            'approved',
        ),
    )
    ->withSortField(
        new SortField(
            'title',
            SortDirection::Ascending,
        ),
    )
    ->withPagination(
        Pagination::firstPage(10),
    )
    ->withProjection(
        new Projection([
            'id',
            'title',
            'status',
        ]),
    );

$results = $repository->query($query);

echo 'Connection: ' . $connection->name()->value() . PHP_EOL;
echo 'Connection open: ' . ($connection->isOpen() ? 'yes' : 'no') . PHP_EOL;
echo 'Transaction state: ' . $transactionManager->state()->value . PHP_EOL;
echo 'Stored documents: ' . $storage->count('documents') . PHP_EOL;
echo 'Approved documents: ' . $results->count() . PHP_EOL;

foreach ($results as $document) {
    echo sprintf(
        '- %d | %s | %s',
        $document->id,
        $document->title,
        $document->status,
    ) . PHP_EOL;
}
