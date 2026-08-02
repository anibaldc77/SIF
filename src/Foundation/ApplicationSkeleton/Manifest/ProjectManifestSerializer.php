<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Manifest;

use JsonException;
use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;

final readonly class ProjectManifestSerializer
{
    public function toJson(ProjectManifest $manifest): string
    {
        try {
            return json_encode(
                $manifest->toArray(),
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ) . "\n";
        } catch (JsonException $exception) {
            throw new InvalidProjectManifestException(
                'The project manifest could not be serialized.',
                previous: $exception,
            );
        }
    }
}
