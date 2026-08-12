<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Keys;
enum CredentialTrustKeyMaterialStatus:string { case Active='active'; case Rotating='rotating'; case Retired='retired'; case Revoked='revoked'; }
