<?php
declare(strict_types=1); namespace Sif\Foundation\Security\Contracts; use Sif\Foundation\Security\VerifiableCredentials\Trust\Anchors\CredentialTrustAnchor; interface CredentialTrustAnchorResolverInterface { public function resolve(string $anchorId): ?CredentialTrustAnchor; }
