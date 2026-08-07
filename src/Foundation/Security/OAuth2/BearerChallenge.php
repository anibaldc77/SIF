<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

use InvalidArgumentException;

final readonly class BearerChallenge
{
    private string $realm;

    public function __construct(
        string $realm,
        private ?BearerError $error = null
    ) {
        $normalized = trim($realm);

        if (
            $normalized === ''
            || strlen($normalized) > 160
            || preg_match('/[\x00-\x1F\x7F"]/', $normalized) === 1
        ) {
            throw new InvalidArgumentException(
                'Bearer challenge realm is invalid.'
            );
        }

        $this->realm = $normalized;
    }

    public function headerValue(): string
    {
        $parts = ['Bearer realm="' . $this->escape($this->realm) . '"'];

        if ($this->error !== null) {
            $parts[] = 'error="' . $this->escape($this->error->code()->value) . '"';

            if ($this->error->description() !== null) {
                $parts[] = 'error_description="'
                    . $this->escape($this->error->description())
                    . '"';
            }

            if ($this->error->scope() !== null) {
                $parts[] = 'scope="' . $this->escape($this->error->scope()) . '"';
            }
        }

        return implode(', ', $parts);
    }

    private function escape(string $value): string
    {
        return addcslashes($value, "\\\"");
    }
}
