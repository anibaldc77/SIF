<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim;

use Sif\Foundation\Security\Scim\Filter\ScimFilterNodeInterface;

final readonly class ScimQuery
{
    /**
     * @param list<string> $attributes
     * @param list<string> $excludedAttributes
     */
    public function __construct(
        private ?ScimFilterNodeInterface $filter = null,
        private ?ScimSort $sort = null,
        private ?ScimPagination $pagination = null,
        private array $attributes = [],
        private array $excludedAttributes = []
    ) {
    }

    public function filter(): ?ScimFilterNodeInterface
    {
        return $this->filter;
    }

    public function sort(): ?ScimSort
    {
        return $this->sort;
    }

    public function pagination(): ?ScimPagination
    {
        return $this->pagination;
    }

    /** @return list<string> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return list<string> */
    public function excludedAttributes(): array
    {
        return $this->excludedAttributes;
    }
}
