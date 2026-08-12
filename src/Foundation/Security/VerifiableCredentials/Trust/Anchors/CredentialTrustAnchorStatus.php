<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Anchors;
enum CredentialTrustAnchorStatus: string { case Active='active'; case Suspended='suspended'; case Retired='retired'; case Revoked='revoked'; }
