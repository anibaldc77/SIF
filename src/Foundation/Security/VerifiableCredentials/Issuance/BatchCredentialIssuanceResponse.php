<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class BatchCredentialIssuanceResponse
{
    /**
     * @param list<CredentialIssuanceResponse> $responses
     */
    public function __construct(private array $responses)
    {
        if ($this->responses === []) {
            throw new InvalidArgumentException(
                'Batch credential issuance response is invalid.'
            );
        }
    }

    /**
     * @return list<CredentialIssuanceResponse>
     */
    public function responses(): array
    {
        return $this->responses;
    }

    public function hasDeferredItems(): bool
    {
        foreach ($this->responses as $response) {
            if ($response->deferred()) {
                return true;
            }
        }

        return false;
    }
}
