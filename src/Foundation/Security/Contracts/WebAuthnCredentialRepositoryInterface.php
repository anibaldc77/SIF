<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;

interface WebAuthnCredentialRepositoryInterface
{
    public function find(string $credentialId): ?WebAuthnCredential;

    /**
     * @return list<WebAuthnCredential>
     */
    public function findByUserHandle(string $userHandle): array;

    public function save(WebAuthnCredential $credential): void;

    public function delete(string $credentialId): void;
}
