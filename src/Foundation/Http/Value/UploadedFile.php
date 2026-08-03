<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Contracts\UploadedFileInterface;
use Sif\Foundation\Http\Exceptions\InvalidUploadedFileException;

final readonly class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        private ?string $clientFilename,
        private ?string $clientMediaType,
        private ?string $temporaryPath,
        private ?int $size,
        private int $error,
    ) {
        if ($clientFilename !== null && preg_match('/[\x00-\x1F\x7F]/', $clientFilename) === 1) {
            throw new InvalidUploadedFileException('Uploaded file client filename contains control characters.');
        }
        if ($clientMediaType !== null && preg_match('~^[A-Za-z0-9!#$&^_.+-]+/[A-Za-z0-9!#$&^_.+-]+$~', $clientMediaType) !== 1) {
            throw new InvalidUploadedFileException(sprintf('Invalid uploaded file media type "%s".', $clientMediaType));
        }
        if ($temporaryPath !== null && ($temporaryPath === '' || preg_match('/[\x00\r\n]/', $temporaryPath) === 1)) {
            throw new InvalidUploadedFileException('Uploaded file temporary path is invalid.');
        }
        if ($size !== null && $size < 0) {
            throw new InvalidUploadedFileException('Uploaded file size must be non-negative.');
        }
        if ($error < 0 || $error > 8) {
            throw new InvalidUploadedFileException(sprintf('Invalid uploaded file error code "%d".', $error));
        }
    }

    public function clientFilename(): ?string { return $this->clientFilename; }
    public function clientMediaType(): ?string { return $this->clientMediaType; }
    public function temporaryPath(): ?string { return $this->temporaryPath; }
    public function size(): ?int { return $this->size; }
    public function error(): int { return $this->error; }
    public function isSuccessful(): bool { return $this->error === UPLOAD_ERR_OK; }
}
