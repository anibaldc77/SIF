<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Diagnostics;

use Sif\Foundation\Security\Authorization\AuthorizationDecision;

final readonly class AuthorizationDiagnosticSnapshot
{
    public function __construct(
        private AuthorizationDecision $decision,
        private string $identityFingerprint,
        private int $roleCount,
        private int $permissionCount,
        private string $evaluationFingerprint
    ) {
    }

    public function decision(): AuthorizationDecision
    {
        return $this->decision;
    }

    /**
     * @return array{
     *     allowed:bool,
     *     failure_reason:string,
     *     identity_fingerprint:string,
     *     role_count:int,
     *     permission_count:int,
     *     evaluation_fingerprint:string
     * }
     */
    public function toArray(): array
    {
        return [
            'allowed' => $this->decision->isAllowed(),
            'failure_reason' => $this->decision->reason()->value,
            'identity_fingerprint' => $this->identityFingerprint,
            'role_count' => $this->roleCount,
            'permission_count' => $this->permissionCount,
            'evaluation_fingerprint' => $this->evaluationFingerprint,
        ];
    }
}
