<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Keys;
use DateTimeImmutable; use InvalidArgumentException;
final readonly class CredentialTrustKeyMaterial {
/** @param list<string> $usages @param array<string,mixed> $metadata */
/**

 * @param array<int, string> $usages
     * @param array<string, mixed> $metadata

 */

public function __construct(private string $keyId, private string $ownerEntityId, private CredentialTrustKeyMaterialStatus $status, private DateTimeImmutable $validFrom, private ?DateTimeImmutable $validUntil=null, private array $usages=[], private array $metadata=[]){ if(trim($keyId)===''||trim($ownerEntityId)==='') throw new InvalidArgumentException('Credential trust key material is invalid.'); if($validUntil!==null && $validUntil<$validFrom) throw new InvalidArgumentException('Credential trust key material validity interval is invalid.'); }
public function keyId():string{return $this->keyId;} public function ownerEntityId():string{return $this->ownerEntityId;} public function status():CredentialTrustKeyMaterialStatus{return $this->status;} public function validFrom():DateTimeImmutable{return $this->validFrom;} public function validUntil():?DateTimeImmutable{return $this->validUntil;} /** @return list<string> */ public function usages():array{return $this->usages;} /** @return array<string,mixed> */ public function metadata():array{return $this->metadata;}
}
