<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Api;

use InvalidArgumentException;

final readonly class MediaType
{
    private string $type;
    private string $subtype;

    public function __construct(string $type, string $subtype)
    {
        $type = strtolower(trim($type));
        $subtype = strtolower(trim($subtype));

        if (preg_match('/^(?:\*|[a-z0-9!#$&^_.+-]+)$/', $type) !== 1
            || preg_match('/^(?:\*|[a-z0-9!#$&^_.+-]+)$/', $subtype) !== 1
            || ($type === '*' && $subtype !== '*')) {
            throw new InvalidArgumentException('Invalid media type.');
        }

        $this->type = $type;
        $this->subtype = $subtype;
    }

    public static function parse(string $value): self
    {
        $base = trim(explode(';', $value, 2)[0]);
        $parts = explode('/', $base, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Media type must contain type and subtype.');
        }

        return new self($parts[0], $parts[1]);
    }

    public static function json(): self
    {
        return new self('application', 'json');
    }

    public static function problemJson(): self
    {
        return new self('application', 'problem+json');
    }

    public function type(): string
    {
        return $this->type;
    }

    public function subtype(): string
    {
        return $this->subtype;
    }

    public function matches(self $candidate): bool
    {
        return ($this->type === '*' || $this->type === $candidate->type)
            && ($this->subtype === '*' || $this->subtype === $candidate->subtype);
    }

    public function specificity(): int
    {
        return ($this->type === '*' ? 0 : 1) + ($this->subtype === '*' ? 0 : 1);
    }

    public function value(): string
    {
        return $this->type . '/' . $this->subtype;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
