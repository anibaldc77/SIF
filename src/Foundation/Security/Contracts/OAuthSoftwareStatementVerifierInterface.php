<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthSoftwareStatement;

interface OAuthSoftwareStatementVerifierInterface
{
    public function verify(
        string $serializedStatement
    ): OAuthSoftwareStatement;
}
