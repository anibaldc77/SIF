<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocNamespace;

interface IsoMdocNamespacePolicyInterface
{
    public function validate(IsoMdocNamespace $namespace): void;
}
