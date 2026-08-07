<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

use Sif\Foundation\Security\Exceptions\InvalidAccessTokenException;

final readonly class AccessToken
{
    private string $value;

    public function __construct(string $value)
    {
        if (
            strlen($value) < 16
            || strlen($value) > 8192
            || preg_match('/^[\x21-\x7E]+$/', $value) !== 1
        ) {
            throw new InvalidAccessTokenException(
                'Access token must be bounded and contain visible ASCII characters only.'
            );
        }

        $this->value = $value;
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->value);
    }

    public function __toString(): string
    {
        return '[REDACTED]';
    }

    /** @return array{value:string} */
    public function __debugInfo(): array
    {
        return ['value' => '[REDACTED]'];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException('Access tokens cannot be serialized.');
    }
}
