<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface OpenId4VpPresentationQueryValidatorInterface
{
    /**
     * @param array<string, mixed> $presentationQuery
     * @param list<string> $vpTokens
     */
    public function validate(
        array $presentationQuery,
        array $vpTokens
    ): void;
}
