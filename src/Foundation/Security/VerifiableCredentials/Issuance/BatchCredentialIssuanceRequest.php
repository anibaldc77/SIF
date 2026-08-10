<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class BatchCredentialIssuanceRequest
{
    /**
     * @param list<CredentialIssuanceRequest> $requests
     */
    public function __construct(private array $requests)
    {
        if ($this->requests === []) {
            throw new InvalidArgumentException(
                'Batch credential issuance request is invalid.'
            );
        }
    }

    /**
     * @return list<CredentialIssuanceRequest>
     */
    public function requests(): array
    {
        return $this->requests;
    }
}
