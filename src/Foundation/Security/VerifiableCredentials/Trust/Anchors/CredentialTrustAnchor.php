<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Anchors;
use DateTimeImmutable; use InvalidArgumentException; use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
final readonly class CredentialTrustAnchor {
/** @param list<string> $keyMaterialIds @param array<string,mixed> $metadata */
/**

 * @param array<int, string> $keyMaterialIds
     * @param array<string, mixed> $metadata

 */

public function __construct(private string $anchorId, private CredentialTrustEntityReference $entity, private CredentialTrustAnchorStatus $status, private DateTimeImmutable $validFrom, private ?DateTimeImmutable $validUntil=null, private array $keyMaterialIds=[], private array $metadata=[]){ if(trim($anchorId)==='') throw new InvalidArgumentException('Credential trust anchor is invalid.'); if($validUntil!==null && $validUntil<$validFrom) throw new InvalidArgumentException('Credential trust anchor validity interval is invalid.'); }
public function anchorId():string{return $this->anchorId;} public function entity():CredentialTrustEntityReference{return $this->entity;} public function status():CredentialTrustAnchorStatus{return $this->status;} public function validFrom():DateTimeImmutable{return $this->validFrom;} public function validUntil():?DateTimeImmutable{return $this->validUntil;} /** @return list<string> */ public function keyMaterialIds():array{return $this->keyMaterialIds;} /** @return array<string,mixed> */ public function metadata():array{return $this->metadata;}
}
