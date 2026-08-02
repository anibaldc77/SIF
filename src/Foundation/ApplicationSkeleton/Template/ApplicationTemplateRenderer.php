<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\TemplateRenderingException;

final readonly class ApplicationTemplateRenderer
{
    /** @param array<string, string> $variables */
    public function render(ApplicationTemplate $template, array $variables): string
    {
        preg_match_all('/\{\{([a-z][a-z0-9_]*)\}\}/', $template->content(), $matches);
        $required = array_values(array_unique($matches[1]));
        sort($required, SORT_STRING);

        $provided = array_keys($variables);
        sort($provided, SORT_STRING);

        $missing = array_values(array_diff($required, $provided));
        if ($missing !== []) {
            throw new TemplateRenderingException(sprintf(
                'Template "%s" is missing variables: %s.',
                $template->name(),
                implode(', ', $missing),
            ));
        }

        $unknown = array_values(array_diff($provided, $required));
        if ($unknown !== []) {
            throw new TemplateRenderingException(sprintf(
                'Template "%s" received unknown variables: %s.',
                $template->name(),
                implode(', ', $unknown),
            ));
        }

        $replacements = [];
        foreach ($variables as $name => $value) {
            if (str_contains($value, "\r") || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value) === 1) {
                throw new TemplateRenderingException(sprintf('Template variable "%s" contains invalid control characters.', $name));
            }
            $replacements['{{' . $name . '}}'] = $value;
        }

        $rendered = strtr($template->content(), $replacements);
        if (preg_match('/\{\{[a-z][a-z0-9_]*\}\}/', $rendered) === 1) {
            throw new TemplateRenderingException(sprintf('Template "%s" contains unresolved placeholders.', $template->name()));
        }

        return $rendered;
    }
}
