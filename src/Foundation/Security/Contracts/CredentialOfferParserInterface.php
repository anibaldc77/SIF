<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialOffer;

interface CredentialOfferParserInterface
{
    public function parse(string $serializedOffer): CredentialOffer;
}
