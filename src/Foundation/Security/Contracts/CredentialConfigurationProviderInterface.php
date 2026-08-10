<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface CredentialConfigurationProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function configuration(string $configurationId): array;
}
