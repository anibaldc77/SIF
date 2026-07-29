<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidAuthorizedInstallationTargetException;

final readonly class AuthorizedInstallationTarget
{
    private string $root;
    private string $relativePath;

    public function __construct(string $root, string $relativePath)
    {
        $root = strtolower(trim($root));
        if ($root === '' || strlen($root) > 64 || preg_match('/^[a-z][a-z0-9-]*$/D', $root) !== 1) {
            throw new InvalidAuthorizedInstallationTargetException(sprintf('Invalid authorized root "%s".', $root));
        }

        $relativePath = str_replace('\\', '/', trim($relativePath));
        if ($relativePath === '' || str_starts_with($relativePath, '/') || preg_match('/^[A-Za-z]:\//D', $relativePath) === 1) {
            throw new InvalidAuthorizedInstallationTargetException('Installation target must use a non-empty relative path.');
        }

        $segments = explode('/', $relativePath);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, "\0")) {
                throw new InvalidAuthorizedInstallationTargetException(sprintf('Unsafe installation target path "%s".', $relativePath));
            }
        }

        if (strlen($relativePath) > 1024) {
            throw new InvalidAuthorizedInstallationTargetException('Installation target path exceeds the maximum length.');
        }

        $this->root = $root;
        $this->relativePath = implode('/', $segments);
    }

    public function root(): string { return $this->root; }
    public function relativePath(): string { return $this->relativePath; }

    /** @return array{root: string, relative_path: string} */
    public function summary(): array
    {
        return ['root' => $this->root, 'relative_path' => $this->relativePath];
    }
}
