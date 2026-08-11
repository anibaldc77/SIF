<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use InvalidArgumentException;

final readonly class IsoMdocNamespace
{
    /**
     * @param list<IsoMdocDataElement> $elements
     */
    public function __construct(
        private string $name,
        private array $elements = []
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'ISO mdoc namespace is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<IsoMdocDataElement>
     */
    public function elements(): array
    {
        return $this->elements;
    }
}
