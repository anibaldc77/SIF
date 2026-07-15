<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Drivers;

use DateTimeImmutable;
use Sif\Builder\FileSystem\Contracts\FileSystemInterface;
use Sif\Builder\FileSystem\Contracts\TemplateRendererInterface;
use Sif\Builder\FileSystem\DTO\Checksum;
use Sif\Builder\FileSystem\DTO\DirectoryInfo;
use Sif\Builder\FileSystem\DTO\FileInfo;
use Sif\Builder\FileSystem\DTO\Mime;
use Sif\Builder\FileSystem\DTO\TemplateContext;
use Sif\Builder\FileSystem\Exceptions\AlreadyExistsException;
use Sif\Builder\FileSystem\Exceptions\InvalidOperationException;
use Sif\Builder\FileSystem\Exceptions\NotFoundException;
use Sif\Builder\FileSystem\Support\PathNormalizer;
use Sif\Builder\FileSystem\Support\PathValidator;

/** In-memory filesystem intended for isolated tests and generated artifacts. */
final class VirtualFileSystem implements FileSystemInterface
{
    /** @var array<string, array{contents:string,modified:DateTimeImmutable,permissions:int}> */
    private array $files = [];
    /** @var array<string, array{modified:DateTimeImmutable,permissions:int}> */
    private array $directories = ['.' => ['modified' => null, 'permissions' => 0775]];

    public function __construct(private readonly PathNormalizer $normalizer, private readonly PathValidator $validator, private readonly TemplateRendererInterface $renderer)
    { $this->directories['.']['modified'] = new DateTimeImmutable(); }
    public function exists(string $path): bool { $path = $this->normalize($path); return isset($this->files[$path]) || isset($this->directories[$path]); }
    public function read(string $path): string { return $this->file($path)['contents']; }
    public function write(string $path, string $contents): void { $path = $this->normalize($path); $this->ensureParent($path); $this->files[$path] = ['contents' => $contents, 'modified' => new DateTimeImmutable(), 'permissions' => 0664]; }
    public function append(string $path, string $contents): void { $this->write($path, $this->exists($path) && isset($this->files[$this->normalize($path)]) ? $this->read($path).$contents : $contents); }
    public function copy(string $source, string $destination): void
    {
        if ($this->exists($destination)) { throw new AlreadyExistsException("'$destination' already exists."); }
        $source = $this->normalize($source);
        if (isset($this->files[$source])) { $this->write($destination, $this->read($source)); return; }
        $this->directory($source); $this->mirror($source, $destination);
    }
    public function move(string $source, string $destination): void
    {
        $source = $this->normalize($source); $this->copy($source, $destination);
        if (isset($this->files[$source])) { $this->delete($source); return; }
        $this->deleteDirectory($source);
    }
    public function rename(string $path, string $newName): void { $this->validator->assertFileName($newName); $path = $this->normalize($path); $this->move($path, $this->parentOf($path).'/'.$newName); }
    public function delete(string $path): void { $path = $this->normalize($path); $this->file($path); unset($this->files[$path]); }
    public function createDirectory(string $path, int $permissions = 0775): void { $path = $this->normalize($path); if (isset($this->files[$path])) { throw new AlreadyExistsException("'$path' is a file."); } if (isset($this->directories[$path])) { return; } $this->ensureParent($path); $this->directories[$path] = ['modified' => new DateTimeImmutable(), 'permissions' => $permissions]; }
    public function deleteDirectory(string $path): void { $path = $this->normalize($path); if (!isset($this->directories[$path])) { throw new NotFoundException("Directory '$path' does not exist."); } foreach (array_keys($this->files) as $file) { if ($file === $path || str_starts_with($file, $path.'/')) { unset($this->files[$file]); } } foreach (array_reverse(array_keys($this->directories)) as $directory) { if ($directory === $path || str_starts_with($directory, $path.'/')) { unset($this->directories[$directory]); } } }
    public function mirror(string $source, string $destination): void { $this->directory($source); $source = $this->normalize($source); $destination = $this->normalize($destination); $this->createDirectory($destination); foreach ($this->files($source, true) as $file) { $relative = $this->relative($source, $file->path); $this->write($destination.'/'.$relative, $this->read($file->path)); } }
    public function files(string $path, bool $recursive = false): iterable { $this->directory($path); $path = $this->normalize($path); foreach (array_keys($this->files) as $file) { if ($this->childOf($file, $path, $recursive)) { yield $this->info($file); } } }
    public function directories(string $path, bool $recursive = false): iterable { $this->directory($path); $path = $this->normalize($path); foreach ($this->directories as $directory => $data) { if ($directory !== $path && $this->childOf($directory, $path, $recursive)) { yield new DirectoryInfo($directory, $data['modified'], $data['permissions']); } } }
    public function relative(string $from, string $to): string { $from = explode('/', $this->normalize($from)); $to = explode('/', $this->normalize($to)); while ($from !== [] && $to !== [] && $from[0] === $to[0]) { array_shift($from); array_shift($to); } return implode('/', array_merge(array_fill(0, count($from), '..'), $to)) ?: '.'; }
    public function normalize(string $path): string { return $this->normalizer->normalize($path); }
    public function checksum(string $path, string $algorithm = 'sha256'): Checksum { if (!in_array($algorithm, hash_algos(), true)) { throw new InvalidOperationException("Invalid checksum algorithm '$algorithm'."); } return new Checksum($algorithm, hash($algorithm, $this->read($path))); }
    public function mime(string $path): Mime { $path = $this->filePath($path); $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)); return new Mime(match ($extension) { 'json' => 'application/json', 'html' => 'text/html', 'txt', 'md' => 'text/plain', 'php' => 'application/x-httpd-php', default => 'application/octet-stream' }); }
    public function size(string $path): int { return strlen($this->read($path)); }
    public function modified(string $path): DateTimeImmutable { $path = $this->normalize($path); return isset($this->files[$path]) ? $this->files[$path]['modified'] : $this->directory($path)['modified']; }
    public function permissions(string $path): int { $path = $this->normalize($path); return isset($this->files[$path]) ? $this->files[$path]['permissions'] : $this->directory($path)['permissions']; }
    public function render(string $path, TemplateContext $context): string { return $this->renderer->render($this->read($path), $context); }
    /** @return array{contents:string,modified:DateTimeImmutable,permissions:int} */ private function file(string $path): array { $path = $this->normalize($path); if (!isset($this->files[$path])) { throw new NotFoundException("File '$path' does not exist."); } return $this->files[$path]; }
    /** @return array{modified:DateTimeImmutable,permissions:int} */ private function directory(string $path): array { $path = $this->normalize($path); if (!isset($this->directories[$path])) { throw new NotFoundException("Directory '$path' does not exist."); } return $this->directories[$path]; }
    private function filePath(string $path): string { $this->file($path); return $this->normalize($path); }
    private function ensureParent(string $path): void { $parent = $this->parentOf($path); if (!isset($this->directories[$parent])) { $this->createDirectory($parent); } }
    private function parentOf(string $path): string { $parent = dirname($path); return $parent === '' ? '.' : str_replace('\\', '/', $parent); }
    private function childOf(string $candidate, string $parent, bool $recursive): bool { $prefix = $parent === '.' ? '' : $parent.'/'; if (!str_starts_with($candidate, $prefix)) { return false; } return $recursive || !str_contains(substr($candidate, strlen($prefix)), '/'); }
    private function info(string $path): FileInfo { $data = $this->file($path); return new FileInfo($path, strlen($data['contents']), $data['modified'], $data['permissions'], $this->mime($path)); }
}
