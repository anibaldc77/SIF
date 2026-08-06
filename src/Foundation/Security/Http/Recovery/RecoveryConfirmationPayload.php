<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryToken;

final readonly class RecoveryConfirmationPayload
{
    private function __construct(private RecoveryChallengeId $challengeId, private RecoveryToken $token) {}

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['challenge_id'], $data['token']) || !is_string($data['challenge_id']) || !is_string($data['token'])) {
            throw new InvalidRecoveryChallengeException('Recovery confirmation payload is invalid.');
        }
        return new self(new RecoveryChallengeId($data['challenge_id']), new RecoveryToken($data['token']));
    }

    public function challengeId(): RecoveryChallengeId { return $this->challengeId; }
    public function token(): RecoveryToken { return $this->token; }
}
