<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

final readonly class ResourceManifestEntry
{
    public function __construct(private PlannedResourcePublication $publication)
    {
    }

    public function publication(): PlannedResourcePublication
    {
        return $this->publication;
    }

    /** @return array{qualified_identifier:string,type:string,source_root:string,source_path:string,target_path:string,priority:int,logical_version:?string,owner:?string,content_sha256:string,content_size:int,publication_order:int} */
    public function canonicalData(): array
    {
        $request = $this->publication->request();
        $descriptor = $request->descriptor();

        return [
            'qualified_identifier' => $descriptor->qualifiedIdentifier(),
            'type' => $descriptor->type()->value(),
            'source_root' => $request->sourceRoot()->value(),
            'source_path' => $descriptor->source()->value(),
            'target_path' => $request->targetPath()->value(),
            'priority' => $descriptor->priority()->value(),
            'logical_version' => $descriptor->logicalVersion(),
            'owner' => $descriptor->owner(),
            'content_sha256' => $request->contentFingerprint()->value(),
            'content_size' => $request->contentSize(),
            'publication_order' => $this->publication->publicationOrder(),
        ];
    }
}
