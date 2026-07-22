<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceReport;

final readonly class ReferenceReportView
{
    /**
     * @param list<ReferenceReportEntryView> $resolved
     * @param list<ReferenceReportEntryView> $broken
     * @param list<DocumentReferenceView> $documents
     * @param array<string, int> $byType
     */
    public function __construct(
        public int $totalDocuments,
        public int $totalReferences,
        public int $resolvedReferences,
        public int $brokenReferences,
        public array $resolved,
        public array $broken,
        public array $documents,
        public array $byType,
    ) {
    }

    /** @return list<DocumentReferenceView> */
    public function isolatedDocuments(): array
    {
        return array_values(array_filter(
            $this->documents,
            static fn (DocumentReferenceView $document): bool => $document->isIsolated(),
        ));
    }

    /** @return list<DocumentReferenceView> */
    public function mostReferencedDocuments(): array
    {
        $documents = array_values(array_filter(
            $this->documents,
            static fn (DocumentReferenceView $document): bool => $document->incoming > 0,
        ));

        usort($documents, static function (DocumentReferenceView $left, DocumentReferenceView $right): int {
            $incoming = $right->incoming <=> $left->incoming;
            return $incoming !== 0 ? $incoming : strnatcasecmp($left->identifier, $right->identifier);
        });

        return $documents;
    }
}
