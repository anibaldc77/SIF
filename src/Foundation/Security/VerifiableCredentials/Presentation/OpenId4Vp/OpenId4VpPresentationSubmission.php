<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpPresentationSubmission
{
    /**
     * @param list<array<string, mixed>> $descriptorMap
     */
    public function __construct(
        private string $id,
        private ?string $definitionId,
        private array $descriptorMap
    ) {
        if (
            trim($this->id) === ''
            || $this->descriptorMap === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP presentation submission is invalid.'
            );
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function definitionId(): ?string
    {
        return $this->definitionId;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function descriptorMap(): array
    {
        return $this->descriptorMap;
    }
}
