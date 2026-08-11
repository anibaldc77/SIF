<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpRequestUri
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || filter_var($this->value, FILTER_VALIDATE_URL) === false
        ) {
            throw new InvalidArgumentException('OpenID4VP request URI is invalid.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
