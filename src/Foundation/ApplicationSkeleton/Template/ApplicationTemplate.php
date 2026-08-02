<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\TemplateRenderingException;

final readonly class ApplicationTemplate
{
    public function __construct(
        private string $name,
        private string $content,
    ) {
        if (preg_match('/^[a-z][a-z0-9.-]*$/', $name) !== 1) {
            throw new TemplateRenderingException(sprintf('Invalid application template name "%s".', $name));
        }

        if (str_contains($content, "\r")) {
            throw new TemplateRenderingException('Application templates must use LF line endings.');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function content(): string
    {
        return $this->content;
    }
}
