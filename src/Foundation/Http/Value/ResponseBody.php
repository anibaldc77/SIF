<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Http\Exceptions\InvalidHttpResponseException;

final readonly class ResponseBody
{
    public function __construct(
        private string $contents = '',
        private ?string $mediaType = null,
        private ?string $charset = null,
    ) {
        if ($mediaType !== null && preg_match('~^[A-Za-z0-9!#$&^_.+-]+/[A-Za-z0-9!#$&^_.+-]+$~', $mediaType) !== 1) {
            throw new InvalidHttpResponseException(sprintf('Invalid response body media type "%s".', $mediaType));
        }
        if ($charset !== null && preg_match('/^[A-Za-z0-9._-]+$/', $charset) !== 1) {
            throw new InvalidHttpResponseException(sprintf('Invalid response body charset "%s".', $charset));
        }
    }

    public function contents(): string { return $this->contents; }
    public function length(): int { return strlen($this->contents); }
    public function mediaType(): ?string { return $this->mediaType; }
    public function charset(): ?string { return $this->charset; }
    public function isEmpty(): bool { return $this->contents === ''; }

    public function contentType(): ?string
    {
        if ($this->mediaType === null) {
            return null;
        }

        return $this->charset === null
            ? $this->mediaType
            : $this->mediaType . '; charset=' . $this->charset;
    }
}
