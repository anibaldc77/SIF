<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

use Sif\Foundation\Resources\Exceptions\InvalidResourcePublicationOrderException;

final readonly class PlannedResourcePublication
{
    public function __construct(
        private ResourcePublicationRequest $request,
        private int $publicationOrder,
    ) {
        if ($publicationOrder < 0) {
            throw new InvalidResourcePublicationOrderException('Resource publication order must be zero or greater.');
        }
    }

    public function request(): ResourcePublicationRequest { return $this->request; }
    public function publicationOrder(): int { return $this->publicationOrder; }
}
