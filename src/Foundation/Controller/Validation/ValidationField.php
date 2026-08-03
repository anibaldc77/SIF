<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

use Sif\Foundation\Controller\Argument\ActionArgumentSource;

final readonly class ValidationField
{
    /** @var list<ValidationRuleInterface> */
    private array $rules;

    /** @param list<ValidationRuleInterface> $rules */
    public function __construct(
        private string $path,
        private ActionArgumentSource $source,
        private string $key,
        array $rules,
    ) {
        if ($path === '' || $key === '') {
            throw new \InvalidArgumentException('Validation field path and key cannot be empty.');
        }
        $this->rules = array_values($rules);
    }

    public function path(): string { return $this->path; }
    public function source(): ActionArgumentSource { return $this->source; }
    public function key(): string { return $this->key; }

    /** @return list<ValidationRuleInterface> */
    public function rules(): array { return $this->rules; }

    public function nullable(): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof Rules\NullableRule) {
                return true;
            }
        }
        return false;
    }
}
