<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Artifact;

use JsonSerializable;
use Sif\Builder\Engine\Exception\InvalidGeneratedArtifactException;

final readonly class GeneratedArtifact implements JsonSerializable
{
    public string $generator;
    public string $relativePath;
    public string $type;

    public function __construct(string $generator, string $relativePath, string $type, public string $content)
    {
        $this->generator = self::identifier($generator, 'Generator');
        $this->type = self::identifier($type, 'Artifact type');
        $this->relativePath = self::path($relativePath);
    }

    public function checksum(): string
    {
        return hash('sha256', $this->content);
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'generator' => $this->generator,
            'relative_path' => $this->relativePath,
            'type' => $this->type,
            'checksum' => $this->checksum(),
        ];
    }

    private static function identifier(string $value, string $label): string
    {
        $value = strtolower(trim($value));
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $value)) {
            throw new InvalidGeneratedArtifactException(sprintf('%s "%s" is invalid.', $label, $value));
        }
        return $value;
    }

    private static function path(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $segments = explode('/', $path);
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)) {
            throw new InvalidGeneratedArtifactException('Artifact path must be relative.');
        }
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidGeneratedArtifactException('Artifact path contains an invalid segment.');
            }
        }
        return implode('/', $segments);
    }
}
