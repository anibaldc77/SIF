<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Localization;

use Sif\Foundation\Resources\Exceptions\InvalidTranslationKeyException;

final readonly class TranslationKey
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || strlen($value) > 255 || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $value) !== 1) {
            throw new InvalidTranslationKeyException(sprintf('Invalid translation key "%s".', $value));
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
