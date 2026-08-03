<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteTransportConstraintException;

final readonly class RouteHostConstraint
{
    private string $template;

    /** @var list<string> */
    private array $parameters;

    public function __construct(string $template)
    {
        $normalized = strtolower(trim($template));
        if ($normalized === '' || str_contains($normalized, '/') || str_contains($normalized, ':')) {
            throw new RouteTransportConstraintException(sprintf('Invalid route host constraint "%s".', $template));
        }
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $normalized, $matches);
        /** @var list<string> $parameters */
        $parameters = $matches[1];
        if (count($parameters) !== count(array_unique($parameters))) {
            throw new RouteTransportConstraintException('Route host constraint contains duplicate placeholders.');
        }
        $probe = preg_replace('/\{[A-Za-z_][A-Za-z0-9_]*\}/', 'segment', $normalized);
        if (!is_string($probe) || filter_var($probe, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            throw new RouteTransportConstraintException(sprintf('Invalid route host constraint "%s".', $template));
        }
        $this->template = $normalized;
        $this->parameters = $parameters;
    }

    public function template(): string { return $this->template; }
    /** @return list<string> */ public function parameters(): array { return $this->parameters; }

    /** @return array<string, string>|null */
    public function match(string $host): ?array
    {
        $pattern = preg_quote($this->template, '~');
        foreach ($this->parameters as $name) {
            $pattern = str_replace(preg_quote('{' . $name . '}', '~'), '(?P<' . $name . '>[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)', $pattern);
        }
        $matches = [];
        if (preg_match('~^' . $pattern . '$~iD', strtolower($host), $matches) !== 1) {
            return null;
        }
        $values = [];
        foreach ($this->parameters as $name) {
            $value = $matches[$name] ?? null;
            if (!is_string($value)) {
                return null;
            }
            $values[$name] = strtolower($value);
        }
        return $values;
    }
}
