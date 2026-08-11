<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use InvalidArgumentException;

final readonly class IsoMdocDataElement
{
    public function __construct(
        private string $identifier,
        private mixed $value
    ) {
        if (trim($this->identifier) === '') {
            throw new InvalidArgumentException(
                'ISO mdoc data element identifier is invalid.'
            );
        }
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
