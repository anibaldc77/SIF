<?php
declare(strict_types=1); namespace Sif\Foundation\Security\Contracts; use Sif\Foundation\Security\VerifiableCredentials\Trust\Keys\CredentialTrustKeyLifecycleTransition; interface CredentialTrustKeyLifecyclePolicyInterface { public function validate(CredentialTrustKeyLifecycleTransition $transition): void; }
