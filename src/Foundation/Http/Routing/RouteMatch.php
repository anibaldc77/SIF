<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

use Sif\Foundation\Http\Exceptions\InvalidRouteDefinitionException;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class RouteMatch
{
    /** @var array<string, string> */
    private array $parameters;

    /** @var list<HttpMethod> */
    private array $allowedMethods;

    /**
     * @param array<string, string> $parameters
     * @param list<HttpMethod> $allowedMethods
     */
    private function __construct(
        private RouteMatchStatus $status,
        private ?RouteDefinition $route,
        array $parameters,
        array $allowedMethods,
    ) {
        if ($status === RouteMatchStatus::Matched && $route === null) {
            throw new InvalidRouteDefinitionException('A matched route result requires a route definition.');
        }
        if ($status !== RouteMatchStatus::Matched && $route !== null) {
            throw new InvalidRouteDefinitionException('An unmatched route result cannot contain a route definition.');
        }
        $this->parameters = $parameters;
        $this->allowedMethods = $allowedMethods;
    }

    /** @param array<string, string> $parameters */
    public static function matched(RouteDefinition $route, array $parameters): self
    {
        return new self(RouteMatchStatus::Matched, $route, $parameters, []);
    }

    public static function notFound(): self
    {
        return new self(RouteMatchStatus::NotFound, null, [], []);
    }

    /** @param list<HttpMethod> $allowedMethods */
    public static function methodNotAllowed(array $allowedMethods): self
    {
        $indexed = [];
        foreach ($allowedMethods as $method) {
            $indexed[$method->value] = $method;
        }
        ksort($indexed);
        return new self(RouteMatchStatus::MethodNotAllowed, null, [], array_values($indexed));
    }

    public function status(): RouteMatchStatus { return $this->status; }
    public function route(): ?RouteDefinition { return $this->route; }
    /** @return array<string, string> */ public function parameters(): array { return $this->parameters; }
    /** @return list<HttpMethod> */ public function allowedMethods(): array { return $this->allowedMethods; }
    public function isMatched(): bool { return $this->status === RouteMatchStatus::Matched; }
}
