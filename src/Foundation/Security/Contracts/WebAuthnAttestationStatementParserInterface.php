<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationStatement;

interface WebAuthnAttestationStatementParserInterface
{
    public function parse(
        string $attestationObject
    ): WebAuthnAttestationStatement;
}
