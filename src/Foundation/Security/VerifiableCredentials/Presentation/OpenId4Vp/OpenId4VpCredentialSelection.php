<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpCredentialSelection
{
    /**
     * @param list<string> $credentialIds
     * @param list<string> $satisfiedRequirementIds
     * @param list<string> $warnings
     */
    public function __construct(
        private string $queryId,
        private array $credentialIds,
        private array $satisfiedRequirementIds,
        private array $warnings = []
    ) {
        if (
            trim($this->queryId) === ''
            || $this->credentialIds === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP credential selection is invalid.'
            );
        }
    }

    public function queryId(): string
    {
        return $this->queryId;
    }

    /**
     * @return list<string>
     */
    public function credentialIds(): array
    {
        return $this->credentialIds;
    }

    /**
     * @return list<string>
     */
    public function satisfiedRequirementIds(): array
    {
        return $this->satisfiedRequirementIds;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
