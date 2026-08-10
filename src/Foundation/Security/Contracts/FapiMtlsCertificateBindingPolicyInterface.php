<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface FapiMtlsCertificateBindingPolicyInterface
{
    public function validate(
        string $clientId,
        string $certificateThumbprint
    ): void;
}
