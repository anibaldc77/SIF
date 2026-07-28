<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Classification;

use Sif\Foundation\ErrorHandling\Contracts\ThrowableClassificationRuleInterface;
use Sif\Foundation\ErrorHandling\Contracts\ThrowableClassifierInterface;
use Sif\Foundation\ErrorHandling\Exceptions\DuplicateClassificationRuleException;
use Throwable;

final readonly class OrderedThrowableClassifier implements ThrowableClassifierInterface
{
    /** @var list<ThrowableClassificationRuleInterface> */
    private array $rules;

    /**
     * @param iterable<ThrowableClassificationRuleInterface> $rules
     */
    public function __construct(
        iterable $rules,
        private ThrowableClassification $fallback,
    ) {
        $normalized = [];
        $names = [];
        foreach ($rules as $rule) {
            if (isset($names[$rule->name()])) {
                throw new DuplicateClassificationRuleException(sprintf(
                    'Classification rule "%s" is already registered.',
                    $rule->name(),
                ));
            }
            $names[$rule->name()] = true;
            $normalized[] = $rule;
        }
        $this->rules = $normalized;
    }

    /** @param iterable<ThrowableClassificationRuleInterface> $rules */
    public static function withUnknownFallback(iterable $rules): self
    {
        return new self($rules, FallbackThrowableClassification::unknown());
    }

    public function classify(Throwable $throwable): ThrowableClassification
    {
        foreach ($this->rules as $rule) {
            $classification = $rule->classify($throwable);
            if ($classification !== null) {
                return $classification;
            }
        }

        return $this->fallback;
    }

    /** @return list<string> */
    public function ruleNames(): array
    {
        return array_map(
            static fn (ThrowableClassificationRuleInterface $rule): string => $rule->name(),
            $this->rules,
        );
    }
}
