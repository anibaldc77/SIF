<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlSignedDocumentPolicy
{
    public function __construct(
        private bool $requireSignedResponse = true,
        private bool $requireSignedAssertion = true
    ) {
    }

    public function requireSignedResponse(): bool
    {
        return $this->requireSignedResponse;
    }

    public function requireSignedAssertion(): bool
    {
        return $this->requireSignedAssertion;
    }
}
