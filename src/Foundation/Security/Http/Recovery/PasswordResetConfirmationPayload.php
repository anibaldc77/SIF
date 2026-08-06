<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryToken;

final readonly class PasswordResetConfirmationPayload
{
    private function __construct(
        private RecoveryChallengeId $challengeId,
        private RecoveryToken $token,
        private PasswordSecret $replacement
    ) {}

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)
            || !isset($data['challenge_id'], $data['token'], $data['password'])
            || !is_string($data['challenge_id'])
            || !is_string($data['token'])
            || !is_string($data['password'])) {
            throw new InvalidRecoveryChallengeException('Password reset confirmation payload is invalid.');
        }

        return new self(
            new RecoveryChallengeId($data['challenge_id']),
            new RecoveryToken($data['token']),
            new PasswordSecret($data['password'])
        );
    }

    public function challengeId(): RecoveryChallengeId { return $this->challengeId; }
    public function token(): RecoveryToken { return $this->token; }
    public function replacement(): PasswordSecret { return $this->replacement; }
}
