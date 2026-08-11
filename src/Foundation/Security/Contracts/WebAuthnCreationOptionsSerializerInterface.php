<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCreationOptions;

interface WebAuthnCreationOptionsSerializerInterface
{
    public function serialize(WebAuthnCreationOptions $options): string;
}
