<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Keys;
use DateTimeImmutable; use InvalidArgumentException;
final readonly class CredentialTrustKeyLifecycleTransition { public function __construct(private CredentialTrustKeyMaterialStatus $from, private CredentialTrustKeyMaterialStatus $to, private DateTimeImmutable $effectiveAt, private string $reason){ if(trim($reason)==='') throw new InvalidArgumentException('Credential trust key lifecycle transition reason is invalid.'); } public function from():CredentialTrustKeyMaterialStatus{return $this->from;} public function to():CredentialTrustKeyMaterialStatus{return $this->to;} public function effectiveAt():DateTimeImmutable{return $this->effectiveAt;} public function reason():string{return $this->reason;} }
