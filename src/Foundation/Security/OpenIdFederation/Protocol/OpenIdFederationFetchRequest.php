<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

use InvalidArgumentException;

final readonly class OpenIdFederationFetchRequest
{
    public function __construct(
        private string $issuerEntityId,
        private string $subjectEntityId
    ) {
        if (
            trim($this->issuerEntityId) === ''
            || trim($this->subjectEntityId) === ''
            || $this->issuerEntityId === $this->subjectEntityId
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation fetch request is invalid.'
            );
        }
    }

    public function issuerEntityId(): string
    {
        return $this->issuerEntityId;
    }

    public function subjectEntityId(): string
    {
        return $this->subjectEntityId;
    }
}
