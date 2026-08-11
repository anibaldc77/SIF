<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpPresentationQuery
{
    /**
     * @param list<OpenId4VpPresentationRequirement> $requirements
     */
    public function __construct(
        private string $queryId,
        private array $requirements
    ) {
        if (
            trim($this->queryId) === ''
            || $this->requirements === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP presentation query is invalid.'
            );
        }
    }

    public function queryId(): string
    {
        return $this->queryId;
    }

    /**
     * @return list<OpenId4VpPresentationRequirement>
     */
    public function requirements(): array
    {
        return $this->requirements;
    }
}
