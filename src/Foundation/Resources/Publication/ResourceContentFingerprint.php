<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

use Sif\Foundation\Resources\Exceptions\InvalidResourceContentFingerprintException;

final readonly class ResourceContentFingerprint
{
    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $value) !== 1) {
            throw new InvalidResourceContentFingerprintException('Resource content fingerprints must be SHA-256 hexadecimal values.');
        }

        $this->value = $value;
    }

    public static function fromContent(string $content): self
    {
        return new self(hash('sha256', $content));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
