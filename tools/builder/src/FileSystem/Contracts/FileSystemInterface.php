<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Contracts;

use DateTimeImmutable;
use Sif\Builder\FileSystem\DTO\Checksum;
use Sif\Builder\FileSystem\DTO\DirectoryInfo;
use Sif\Builder\FileSystem\DTO\FileInfo;
use Sif\Builder\FileSystem\DTO\Mime;
use Sif\Builder\FileSystem\DTO\TemplateContext;

interface FileSystemInterface
{
    public function exists(string $path): bool;
    public function read(string $path): string;
    public function write(string $path, string $contents): void;
    public function append(string $path, string $contents): void;
    public function copy(string $source, string $destination): void;
    public function move(string $source, string $destination): void;
    public function rename(string $path, string $newName): void;
    public function delete(string $path): void;
    public function createDirectory(string $path, int $permissions = 0775): void;
    public function deleteDirectory(string $path): void;
    public function mirror(string $source, string $destination): void;
    /** @return iterable<FileInfo> */
    public function files(string $path, bool $recursive = false): iterable;
    /** @return iterable<DirectoryInfo> */
    public function directories(string $path, bool $recursive = false): iterable;
    public function relative(string $from, string $to): string;
    public function normalize(string $path): string;
    public function checksum(string $path, string $algorithm = 'sha256'): Checksum;
    public function mime(string $path): Mime;
    public function size(string $path): int;
    public function modified(string $path): DateTimeImmutable;
    public function permissions(string $path): int;
    public function render(string $path, TemplateContext $context): string;
}
