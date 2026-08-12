<?php
declare(strict_types=1); namespace Sif\Foundation\Security\Contracts; use Sif\Foundation\Security\VerifiableCredentials\Trust\Anchors\CredentialTrustAnchor; use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile; interface CredentialTrustAnchorPolicyInterface { public function validate(CredentialTrustAnchor $anchor, CredentialTrustProfile $profile): void; }
