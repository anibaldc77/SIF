<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Drivers;

use DateTimeImmutable;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sif\Builder\FileSystem\Contracts\FileSystemInterface;
use Sif\Builder\FileSystem\Contracts\TemplateRendererInterface;
use Sif\Builder\FileSystem\DTO\Checksum;
use Sif\Builder\FileSystem\DTO\DirectoryInfo;
use Sif\Builder\FileSystem\DTO\FileInfo;
use Sif\Builder\FileSystem\DTO\Mime;
use Sif\Builder\FileSystem\DTO\TemplateContext;
use Sif\Builder\FileSystem\Exceptions\AlreadyExistsException;
use Sif\Builder\FileSystem\Exceptions\InvalidOperationException;
use Sif\Builder\FileSystem\Exceptions\IoException;
use Sif\Builder\FileSystem\Exceptions\NotFoundException;
use Sif\Builder\FileSystem\Support\PathNormalizer;
use Sif\Builder\FileSystem\Support\PathValidator;
use SplFileInfo;

final class LocalFileSystem implements FileSystemInterface
{
    public function __construct(
        private readonly PathNormalizer $normalizer,
        private readonly PathValidator $validator,
        private readonly TemplateRendererInterface $renderer,
        private readonly ?string $root = null,
    ) {}

    public function exists(string $path): bool { return file_exists($this->path($path)); }
    public function read(string $path): string
    {
        $path = $this->file($path); $contents = file_get_contents($path);
        if ($contents === false) { throw new IoException("Unable to read '$path'."); }
        return $contents;
    }
    public function write(string $path, string $contents): void
    {
        $path = $this->path($path); $this->parent($path);
        if (file_put_contents($path, $contents, LOCK_EX) === false) { throw new IoException("Unable to write '$path'."); }
    }
    public function append(string $path, string $contents): void
    {
        $path = $this->path($path); $this->parent($path);
        if (file_put_contents($path, $contents, FILE_APPEND | LOCK_EX) === false) { throw new IoException("Unable to append '$path'."); }
    }
    public function copy(string $source, string $destination): void
    {
        $source = $this->file($source); $destination = $this->path($destination); $this->parent($destination);
        if (file_exists($destination)) { throw new AlreadyExistsException("Destination '$destination' already exists."); }
        if (!copy($source, $destination)) { throw new IoException("Unable to copy '$source'."); }
    }
    public function move(string $source, string $destination): void
    {
        $source = $this->path($source); if (!file_exists($source)) { throw new NotFoundException("'$source' does not exist."); }
        $destination = $this->path($destination); $this->parent($destination);
        if (file_exists($destination)) { throw new AlreadyExistsException("Destination '$destination' already exists."); }
        if (!rename($source, $destination)) { throw new IoException("Unable to move '$source'."); }
    }
    public function rename(string $path, string $newName): void
    {
        $this->validator->assertFileName($newName); $path = $this->path($path);
        $this->move($path, dirname($path).DIRECTORY_SEPARATOR.$newName);
    }
    public function delete(string $path): void
    {
        $path = $this->file($path); if (!unlink($path)) { throw new IoException("Unable to delete '$path'."); }
    }
    public function createDirectory(string $path, int $permissions = 0775): void
    {
        $path = $this->path($path); if (is_dir($path)) { return; }
        if (file_exists($path)) { throw new AlreadyExistsException("'$path' is a file."); }
        if (!mkdir($path, $permissions, true) && !is_dir($path)) { throw new IoException("Unable to create '$path'."); }
    }
    public function deleteDirectory(string $path): void
    {
        $path = $this->directory($path);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) { $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname()); }
        if (!rmdir($path)) { throw new IoException("Unable to delete '$path'."); }
    }
    public function mirror(string $source, string $destination): void
    {
        $source = $this->directory($source); $destination = $this->path($destination); $this->createDirectory($destination);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $entry) { $relative = substr($entry->getPathname(), strlen($source) + 1); $target = $destination.DIRECTORY_SEPARATOR.$relative; if ($entry->isDir()) { $this->createDirectory($target); } else { $this->write($target, $this->read($entry->getPathname())); } }
    }
    public function files(string $path, bool $recursive = false): iterable
    {
        $path = $this->directory($path); $iterator = $recursive ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) : new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $entry) { if ($entry->isFile()) { yield $this->fileInfo($entry); } }
    }
    public function directories(string $path, bool $recursive = false): iterable
    {
        $path = $this->directory($path); $iterator = $recursive ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) : new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $entry) { if ($entry->isDir()) { yield new DirectoryInfo($entry->getPathname(), (new DateTimeImmutable())->setTimestamp($entry->getMTime()), $entry->getPerms() & 0777); } }
    }
    public function relative(string $from, string $to): string
    {
        $from = explode('/', str_replace('\\', '/', $this->path($from))); $to = explode('/', str_replace('\\', '/', $this->path($to)));
        while ($from !== [] && $to !== [] && $from[0] === $to[0]) { array_shift($from); array_shift($to); }
        return implode('/', array_merge(array_fill(0, count($from), '..'), $to)) ?: '.';
    }
    public function normalize(string $path): string { return $this->normalizer->normalize($path); }
    public function checksum(string $path, string $algorithm = 'sha256'): Checksum
    {
        $path = $this->file($path); $value = hash_file($algorithm, $path); if ($value === false) { throw new InvalidOperationException("Checksum algorithm '$algorithm' is not available."); } return new Checksum($algorithm, $value);
    }
    public function mime(string $path): Mime
    {
        $path = $this->file($path); $detector = new \finfo(FILEINFO_MIME_TYPE); $value = $detector->file($path); if ($value === false) { throw new IoException("Unable to determine MIME type for '$path'."); } return new Mime($value);
    }
    public function size(string $path): int { $size = filesize($this->file($path)); if ($size === false) { throw new IoException('Unable to obtain file size.'); } return $size; }
    public function modified(string $path): DateTimeImmutable { $time = filemtime($this->path($path)); if ($time === false) { throw new IoException('Unable to obtain modification time.'); } return (new DateTimeImmutable())->setTimestamp($time); }
    public function permissions(string $path): int { $permissions = fileperms($this->path($path)); if ($permissions === false) { throw new IoException('Unable to obtain permissions.'); } return $permissions & 0777; }
    public function render(string $path, TemplateContext $context): string { return $this->renderer->render($this->read($path), $context); }

    private function path(string $path): string
    {
        $path = $this->normalizer->normalize($path);
        if ($this->root === null) { return $path; }
        $root = $this->normalizer->normalize($this->root);
        return $path === $root || str_starts_with($path, $root.'/') ? $path : $this->normalizer->normalize($root.'/'.$path);
    }
    private function file(string $path): string { $path = $this->path($path); if (!is_file($path)) { throw new NotFoundException("File '$path' does not exist."); } return $path; }
    private function directory(string $path): string { $path = $this->path($path); if (!is_dir($path)) { throw new NotFoundException("Directory '$path' does not exist."); } return $path; }
    private function parent(string $path): void { $parent = dirname($path); if (!is_dir($parent)) { $this->createDirectory($parent); } }
    private function fileInfo(SplFileInfo $entry): FileInfo { return new FileInfo($entry->getPathname(), $entry->getSize(), (new DateTimeImmutable())->setTimestamp($entry->getMTime()), $entry->getPerms() & 0777, $this->mime($entry->getPathname())); }
}
