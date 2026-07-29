<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Exceptions;

final class TranslationNotFoundException extends ResourceException
{
    public static function forKey(string $key, string $locale, string $namespace): self
    {
        return new self(sprintf(
            'Translation "%s" was not found for locale "%s" in namespace "%s".',
            $key,
            $locale,
            $namespace,
        ));
    }
}
