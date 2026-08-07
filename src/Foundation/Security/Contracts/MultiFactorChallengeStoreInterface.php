<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\MultiFactor\MultiFactorChallenge;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;

interface MultiFactorChallengeStoreInterface
{
    public function save(MultiFactorChallenge $challenge): void;

    public function find(MultiFactorChallengeId $id): ?MultiFactorChallenge;
}
