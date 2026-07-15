<?php
declare(strict_types=1);
namespace Sif\Builder\FileSystem\Services;
use Sif\Builder\FileSystem\Contracts\TemplateRendererInterface;
use Sif\Builder\FileSystem\DTO\TemplateContext;
final class TemplateRenderer implements TemplateRendererInterface
{
    public function render(string $template, TemplateContext $context): string
    {
        $replacements = [];
        foreach ($context->values() as $name => $value) { $replacements['{{'.$name.'}}'] = $value; }
        return strtr($template, $replacements);
    }
}
