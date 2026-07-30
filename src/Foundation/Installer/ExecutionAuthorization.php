<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidExecutionAuthorizationException;

final readonly class ExecutionAuthorization
{
    private string $authorizationIdentifier;
    private InstallationIdentifier $installationIdentifier;
    private string $planFingerprint;
    private bool $mutationAllowed;

    public function __construct(
        string $authorizationIdentifier,
        InstallationIdentifier $installationIdentifier,
        string $planFingerprint,
        bool $mutationAllowed,
    ) {
        $authorizationIdentifier = trim($authorizationIdentifier);
        if (
            $authorizationIdentifier === ''
            || strlen($authorizationIdentifier) > 128
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $authorizationIdentifier) !== 1
        ) {
            throw new InvalidExecutionAuthorizationException('Execution authorization requires a stable identifier.');
        }
        if (preg_match('/^[a-f0-9]{64}$/D', $planFingerprint) !== 1) {
            throw new InvalidExecutionAuthorizationException('Execution authorization requires a lowercase SHA-256 plan fingerprint.');
        }

        $this->authorizationIdentifier = $authorizationIdentifier;
        $this->installationIdentifier = $installationIdentifier;
        $this->planFingerprint = $planFingerprint;
        $this->mutationAllowed = $mutationAllowed;
    }

    public function authorizationIdentifier(): string { return $this->authorizationIdentifier; }
    public function installationIdentifier(): InstallationIdentifier { return $this->installationIdentifier; }
    public function planFingerprint(): string { return $this->planFingerprint; }
    public function mutationAllowed(): bool { return $this->mutationAllowed; }

    /** @return array{authorization_identifier:string,installation_identifier:string,plan_fingerprint:string,mutation_allowed:bool} */
    public function summary(): array
    {
        return [
            'authorization_identifier' => $this->authorizationIdentifier,
            'installation_identifier' => $this->installationIdentifier->value(),
            'plan_fingerprint' => $this->planFingerprint,
            'mutation_allowed' => $this->mutationAllowed,
        ];
    }
}
