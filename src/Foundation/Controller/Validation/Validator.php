<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

final class Validator
{
    public function validate(ValidationSchema $schema, ValidationContext $context): ValidationResult
    {
        $issues = [];
        foreach ($schema->fields() as $field) {
            $value = $context->input()->value($field->source(), $field->key());
            if ($value->present() && $value->value() === null && $field->nullable()) {
                continue;
            }
            foreach ($field->rules() as $rule) {
                foreach ($rule->validate($value, $context, $field->path()) as $issue) {
                    $issues[] = $issue;
                }
            }
        }
        return new ValidationResult($issues);
    }
}
