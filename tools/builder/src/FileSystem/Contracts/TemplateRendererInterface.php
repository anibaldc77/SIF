<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\Contracts;

use Sif\Builder\FileSystem\DTO\TemplateContext;

interface TemplateRendererInterface
{
    public function render(string $template, TemplateContext $context): string;
}
