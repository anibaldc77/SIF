<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final class MarkdownRepositoryScanner implements RepositoryScannerInterface
{
    public function __construct(
        private readonly MetadataReaderInterface $reader,
        private readonly MetadataValidatorInterface $validator,
    ) {
    }

    public function scan(string $root): MetadataScanResult
    {
        $registry = new MetadataRegistry();
        $issues = [];

        if (!is_dir($root)) {
            return new MetadataScanResult($registry, [new MetadataScanIssue($root, 'Repository root is not a directory.')]);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (!$file->isFile() || !$this->reader->supports($path)) {
                continue;
            }

            try {
                $document = $this->reader->read($path);
                $validation = $this->validator->validate($document->metadata);
                if (!$validation->isValid()) {
                    foreach ($validation->errors() as $error) {
                        $issues[] = new MetadataScanIssue($path, sprintf('%s [%s]: %s', $error->code, $error->path, $error->message));
                    }
                    continue;
                }
                $registry->register($document);
            } catch (Throwable $exception) {
                $issues[] = new MetadataScanIssue($path, $exception->getMessage());
            }
        }

        return new MetadataScanResult($registry, $issues);
    }
}
