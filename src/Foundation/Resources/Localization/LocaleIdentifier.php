<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Exceptions\InvalidLocaleIdentifierException;

final readonly class LocaleIdentifier
{
    private string $value;

    public function __construct(string $value)
    {
        $value = str_replace('_', '-', trim($value));
        if ($value === '' || strlen($value) > 64 || preg_match('/^[A-Za-z]{2,8}(?:-[A-Za-z0-9]{1,8})*$/D', $value) !== 1) {
            throw new InvalidLocaleIdentifierException(sprintf('Invalid locale identifier "%s".', $value));
        }

        $parts = explode('-', $value);
        $canonical = [strtolower(array_shift($parts))];
        foreach ($parts as $part) {
            if (strlen($part) === 4 && ctype_alpha($part)) {
                $canonical[] = ucfirst(strtolower($part));
                continue;
            }
            if ((strlen($part) === 2 && ctype_alpha($part)) || (strlen($part) === 3 && ctype_digit($part))) {
                $canonical[] = strtoupper($part);
                continue;
            }
            $canonical[] = strtolower($part);
        }

        $this->value = implode('-', $canonical);
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    /** @return list<self> */
    public function hierarchy(): array
    {
        $parts = explode('-', $this->value);
        $locales = [];
        while ($parts !== []) {
            $locales[] = new self(implode('-', $parts));
            array_pop($parts);
        }

        return $locales;
    }

    public function __toString(): string { return $this->value; }
}
