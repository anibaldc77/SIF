<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Exceptions\InvalidMultiFactorChallengeException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class MultiFactorChallenge
{
    private DateTimeImmutable $issuedAt;
    private DateTimeImmutable $expiresAt;

    public function __construct(
        private MultiFactorChallengeId $id,
        private IdentityId $identityId,
        private MultiFactorType $factorType,
        private MultiFactorChallengePurpose $purpose,
        private AuthenticationLevel $requiredLevel,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt,
        private MultiFactorChallengeStatus $status = MultiFactorChallengeStatus::Pending
    ) {
        $utc = new DateTimeZone('UTC');
        $normalizedIssuedAt = $issuedAt->setTimezone($utc);
        $normalizedExpiresAt = $expiresAt->setTimezone($utc);

        if ($normalizedExpiresAt <= $normalizedIssuedAt) {
            throw new InvalidMultiFactorChallengeException(
                'Multi-factor challenge expiration must be later than issuance.'
            );
        }
        if ($requiredLevel->value() < 1) {
            throw new InvalidMultiFactorChallengeException(
                'Multi-factor challenge must require a positive authentication level.'
            );
        }
        $this->issuedAt = $normalizedIssuedAt;
        $this->expiresAt = $normalizedExpiresAt;
    }

    public function id(): MultiFactorChallengeId { return $this->id; }
    public function identityId(): IdentityId { return $this->identityId; }
    public function factorType(): MultiFactorType { return $this->factorType; }
    public function purpose(): MultiFactorChallengePurpose { return $this->purpose; }
    public function requiredLevel(): AuthenticationLevel { return $this->requiredLevel; }
    public function status(): MultiFactorChallengeStatus { return $this->status; }
    public function issuedAt(): DateTimeImmutable { return $this->issuedAt; }
    public function expiresAt(): DateTimeImmutable { return $this->expiresAt; }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $now->setTimezone(new DateTimeZone('UTC')) >= $this->expiresAt;
    }

    public function satisfy(): self
    {
        if ($this->status !== MultiFactorChallengeStatus::Pending) {
            throw new InvalidMultiFactorChallengeException('Only pending challenges can be satisfied.');
        }
        return new self($this->id, $this->identityId, $this->factorType, $this->purpose,
            $this->requiredLevel, $this->issuedAt, $this->expiresAt, MultiFactorChallengeStatus::Satisfied);
    }

    public function revoke(): self
    {
        if ($this->status !== MultiFactorChallengeStatus::Pending) {
            return $this;
        }
        return new self($this->id, $this->identityId, $this->factorType, $this->purpose,
            $this->requiredLevel, $this->issuedAt, $this->expiresAt, MultiFactorChallengeStatus::Revoked);
    }

    /** @return array{id:string,identity_fingerprint:string,factor_type:string,purpose:string,required_level:int,status:string,issued_at:string,expires_at:string} */
    public function snapshot(): array
    {
        return [
            'id'=>$this->id->value(),
            'identity_fingerprint'=>hash('sha256',$this->identityId->value()),
            'factor_type'=>$this->factorType->value(),
            'purpose'=>$this->purpose->value,
            'required_level'=>$this->requiredLevel->value(),
            'status'=>$this->status->value,
            'issued_at'=>$this->issuedAt->format(DATE_ATOM),
            'expires_at'=>$this->expiresAt->format(DATE_ATOM),
        ];
    }
}
