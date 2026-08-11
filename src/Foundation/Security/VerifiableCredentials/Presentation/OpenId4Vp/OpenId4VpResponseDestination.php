<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpResponseDestination
{
    public function __construct(
        private string $uri,
        private OpenId4VpResponseMode $mode
    ) {
        if (
            trim($this->uri) === ''
            || filter_var($this->uri, FILTER_VALIDATE_URL) === false
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP response destination is invalid.'
            );
        }
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function mode(): OpenId4VpResponseMode
    {
        return $this->mode;
    }
}
