<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class VerifiableCredentialSubject
{
    /**
     * @param array<string, scalar|list<scalar>|null> $claims
     */
    public function __construct(
        private string $subjectId,
        private array $claims = []
    ) {
        if (trim($this->subjectId) === '') {
            throw new InvalidArgumentException(
                'Verifiable credential subject id is invalid.'
            );
        }
    }

    public function subjectId(): string
    {
        return $this->subjectId;
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function claims(): array
    {
        return $this->claims;
    }
}
