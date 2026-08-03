<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface UploadedFileInterface
{
    public function clientFilename(): ?string;
    public function clientMediaType(): ?string;
    public function temporaryPath(): ?string;
    public function size(): ?int;
    public function error(): int;
    public function isSuccessful(): bool;
}
