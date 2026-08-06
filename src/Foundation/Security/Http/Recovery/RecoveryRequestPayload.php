<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;

final readonly class RecoveryRequestPayload
{
    private function __construct(private IdentityLookupKey $lookupKey) {}

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['identity']) || !is_string($data['identity'])) {
            throw new InvalidRecoveryChallengeException('Recovery request payload must contain an identity string.');
        }
        return new self(new IdentityLookupKey($data['identity']));
    }

    public function lookupKey(): IdentityLookupKey { return $this->lookupKey; }
}
