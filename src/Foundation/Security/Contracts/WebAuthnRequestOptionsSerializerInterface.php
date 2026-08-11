<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnRequestOptions;

interface WebAuthnRequestOptionsSerializerInterface
{
    public function serialize(
        WebAuthnRequestOptions $options
    ): string;
}
