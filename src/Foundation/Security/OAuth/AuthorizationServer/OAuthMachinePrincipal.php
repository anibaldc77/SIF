<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthMachinePrincipal
{
    public function __construct(
        private string $subject,
        private OAuthClientId $clientId
    ) {
        if (trim($this->subject) === '') {
            throw new InvalidArgumentException(
                'OAuth machine principal subject is invalid.'
            );
        }
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function clientId(): OAuthClientId
    {
        return $this->clientId;
    }
}
