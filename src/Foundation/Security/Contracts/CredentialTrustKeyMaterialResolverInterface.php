<?php
declare(strict_types=1); namespace Sif\Foundation\Security\Contracts; use Sif\Foundation\Security\VerifiableCredentials\Trust\Keys\CredentialTrustKeyMaterial; interface CredentialTrustKeyMaterialResolverInterface { public function resolve(string $keyId): ?CredentialTrustKeyMaterial; }
