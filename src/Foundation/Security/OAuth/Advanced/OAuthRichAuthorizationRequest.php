<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthRichAuthorizationRequest
{
    /**
     * @param list<OAuthAuthorizationDetail> $authorizationDetails
     */
    public function __construct(
        private array $authorizationDetails
    ) {
        if ($this->authorizationDetails === []) {
            throw new InvalidArgumentException(
                'OAuth rich authorization request requires authorization details.'
            );
        }
    }

    /**
     * @return list<OAuthAuthorizationDetail>
     */
    public function authorizationDetails(): array
    {
        return $this->authorizationDetails;
    }

    public function hasType(
        OAuthAuthorizationDetailType $type
    ): bool {
        foreach ($this->authorizationDetails as $detail) {
            if ($detail->type()->value() === $type->value()) {
                return true;
            }
        }

        return false;
    }
}
