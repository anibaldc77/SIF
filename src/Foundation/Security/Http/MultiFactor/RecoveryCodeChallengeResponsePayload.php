<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\MultiFactor;

use JsonException;
use Sif\Foundation\Security\Exceptions\InvalidMultiFactorRequestException;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCode;

final readonly class RecoveryCodeChallengeResponsePayload
{
    private function __construct(
        private MultiFactorChallengeId $challengeId,
        private RecoveryCode $code
    ) {
    }

    public static function fromJson(string $json): self
    {
        try {
            $payload = json_decode($json, true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidMultiFactorRequestException(
                'Recovery-code challenge response must contain valid JSON.',
                previous: $exception
            );
        }

        if (!is_array($payload)) {
            throw new InvalidMultiFactorRequestException(
                'Recovery-code challenge response must be a JSON object.'
            );
        }

        $challengeId = $payload['challenge_id'] ?? null;
        $code = $payload['code'] ?? null;

        if (!is_string($challengeId) || !is_string($code)) {
            throw new InvalidMultiFactorRequestException(
                'Recovery-code challenge response requires string challenge_id and code fields.'
            );
        }

        try {
            return new self(
                new MultiFactorChallengeId($challengeId),
                new RecoveryCode($code)
            );
        } catch (\Throwable $exception) {
            throw new InvalidMultiFactorRequestException(
                'Recovery-code challenge response is invalid.',
                previous: $exception
            );
        }
    }

    public function challengeId(): MultiFactorChallengeId
    {
        return $this->challengeId;
    }

    public function code(): RecoveryCode
    {
        return $this->code;
    }

    /** @return array{challenge_id:string,code:string} */
    public function __debugInfo(): array
    {
        return [
            'challenge_id' => $this->challengeId->value(),
            'code' => '[REDACTED]',
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException('Recovery-code challenge response payloads cannot be serialized.');
    }
}
