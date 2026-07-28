<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Contracts\RecoveryDeciderInterface;
use Sif\Foundation\ErrorHandling\Contracts\RecoveryPolicyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\DuplicateRecoveryPolicyException;

final readonly class OrderedRecoveryDecider implements RecoveryDeciderInterface
{
    /** @var list<RecoveryPolicyInterface> */
    private array $policies;

    /** @param iterable<RecoveryPolicyInterface> $policies */
    public function __construct(iterable $policies, private RecoveryDecision $fallback)
    {
        $normalized = [];
        $names = [];
        foreach ($policies as $policy) {
            if (isset($names[$policy->name()])) {
                throw new DuplicateRecoveryPolicyException(sprintf(
                    'Recovery policy "%s" is already registered.',
                    $policy->name(),
                ));
            }
            $names[$policy->name()] = true;
            $normalized[] = $policy;
        }
        $this->policies = $normalized;
    }

    /** @param iterable<RecoveryPolicyInterface> $policies */
    public static function withRethrowFallback(iterable $policies): self
    {
        return new self($policies, RecoveryDecision::fallbackRethrow());
    }

    public function decide(ThrowableClassification $classification, int $attempt = 1): RecoveryDecision
    {
        foreach ($this->policies as $policy) {
            $decision = $policy->decide($classification, $attempt);
            if ($decision !== null) {
                return $decision;
            }
        }
        return $this->fallback;
    }

    /** @return list<string> */
    public function policyNames(): array
    {
        return array_map(static fn (RecoveryPolicyInterface $policy): string => $policy->name(), $this->policies);
    }
}
