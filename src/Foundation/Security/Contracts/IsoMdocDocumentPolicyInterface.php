<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDocument;

interface IsoMdocDocumentPolicyInterface
{
    public function validate(IsoMdocDocument $document): void;
}
