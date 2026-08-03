<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class InRule extends AbstractRule implements ValidationRuleInterface
{
    /** @var list<scalar|null> */
    private array $allowed;
    /** @param list<scalar|null> $allowed */
    public function __construct(array $allowed) { $this->allowed = array_values($allowed); }
    public function name(): string { return 'in'; }
    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        if (!$value->present()) return [];
        return in_array($value->value(), $this->allowed, true) ? [] : [$this->issue('validation.in', $path, 'The field contains an unsupported value.')];
    }
}
