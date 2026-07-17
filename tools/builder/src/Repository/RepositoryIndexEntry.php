<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

use InvalidArgumentException;

final readonly class RepositoryIndexEntry
{
    public string $identifier;
    public string $title;
    public string $documentClass;
    public string $category;
    public string $status;
    public string $version;
    public string $path;
    public ?string $workPackage;

    /** @var list<string> */
    public array $tags;

    /**
     * @param list<string> $tags
     */
    public function __construct(
        string $identifier,
        string $title,
        string $documentClass,
        string $category,
        string $status,
        string $version,
        string $path,
        ?string $workPackage = null,
        array $tags = [],
    ) {
        if (trim($identifier) === '') {
            throw new InvalidArgumentException('Repository entry identifier must not be empty.');
        }

        if (trim($path) === '') {
            throw new InvalidArgumentException('Repository entry path must not be empty.');
        }

        $normalizedTags = [];
        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                throw new InvalidArgumentException('Repository entry tags must contain only strings.');
            }

            $tag = trim($tag);
            if ($tag === '' || in_array($tag, $normalizedTags, true)) {
                continue;
            }

            $normalizedTags[] = $tag;
        }

        $this->identifier = trim($identifier);
        $this->title = $title;
        $this->documentClass = $documentClass;
        $this->category = $category;
        $this->status = $status;
        $this->version = $version;
        $this->path = trim($path);
        $this->workPackage = $workPackage;
        $this->tags = $normalizedTags;
    }
}
