<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpPresentationRequirement
{
    /**
     * @param list<string> $acceptedFormats
     * @param array<string, mixed> $constraints
     */
    public function __construct(
        private string $id,
        private array $acceptedFormats = [],
        private array $constraints = [],
        private bool $required = true
    ) {
        if (trim($this->id) === '') {
            throw new InvalidArgumentException(
                'OpenID4VP presentation requirement is invalid.'
            );
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return list<string>
     */
    public function acceptedFormats(): array
    {
        return $this->acceptedFormats;
    }

    /**
     * @return array<string, mixed>
     */
    public function constraints(): array
    {
        return $this->constraints;
    }

    public function required(): bool
    {
        return $this->required;
    }
}
