<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final class MarkdownRepositoryScanner implements RepositoryScannerInterface
{
    /** @var list<string> */
    private const EXCLUDED_DIRECTORY_SEGMENTS = [
        '.git',
        '.idea',
        '.vscode',
        'node_modules',
        'vendor',
        'build',
        'dist',
        'coverage',
        '.cache',
        '.phpunit.cache',
        '.phpstan.cache',
        '.generated',
        'generated',
        'tmp',
        'temp',
    ];

    /** @var list<string> */
    private const EXCLUDED_MARKDOWN_PATHS = [
        'engineering/index.generated.md',
        'engineering/references.generated.md',
        'engineering/navigation.generated.md',
    ];

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

        $root = rtrim($root, "/\\");
        $directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator(
            $directory,
            fn (SplFileInfo $file): bool => !$this->isExcluded($root, $file),
        );
        $iterator = new RecursiveIteratorIterator($filter);
        $candidates = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (!$file->isFile() || !$this->reader->supports($path)) {
                continue;
            }

            $candidates[$this->relativePath($root, $path)] = $path;
        }

        ksort($candidates, SORT_STRING);

        foreach ($candidates as $path) {
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

    private function isExcluded(string $root, SplFileInfo $file): bool
    {
        $relativePath = $this->relativePath($root, $file->getPathname());
        if ($file->isDir()) {
            return in_array(strtolower($file->getFilename()), self::EXCLUDED_DIRECTORY_SEGMENTS, true);
        }

        return in_array(strtolower($relativePath), self::EXCLUDED_MARKDOWN_PATHS, true);
    }

    private function relativePath(string $root, string $path): string
    {
        $normalizedRoot = str_replace('\\', '/', rtrim($root, "/\\"));
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with(strtolower($normalizedPath), strtolower($normalizedRoot . '/'))) {
            $normalizedPath = substr($normalizedPath, strlen($normalizedRoot) + 1);
        }

        $normalizedPath = (string) preg_replace('~/+~', '/', $normalizedPath);

        return str_starts_with($normalizedPath, './') ? substr($normalizedPath, 2) : $normalizedPath;
    }
}
