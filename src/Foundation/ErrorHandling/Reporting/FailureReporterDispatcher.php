<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use InvalidArgumentException;
use Sif\Foundation\ErrorHandling\Contracts\EmergencyFailureReporterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Throwable;

final readonly class FailureReporterDispatcher
{
    /** @var list<FailureReportRoute> */
    private array $routes;

    /** @param list<FailureReportRoute> $routes */
    public function __construct(array $routes, private EmergencyFailureReporterInterface $emergencyReporter)
    {
        if ($routes === []) {
            throw new InvalidArgumentException('At least one failure report route is required.');
        }

        $names = [];
        foreach ($routes as $route) {
            if (isset($names[$route->name()])) {
                throw new InvalidArgumentException(sprintf('Duplicate failure report route "%s".', $route->name()));
            }
            $names[$route->name()] = true;
        }
        $this->routes = array_values($routes);
    }

    public function dispatch(FailureEnvelope $envelope, RecoveryDecision $decision): FailureReportingResult
    {
        $reported = [];
        $filtered = [];
        $failures = [];

        foreach ($this->routes as $route) {
            if (!$route->filter()->accepts($envelope, $decision)) {
                $filtered[] = $route->name();
                continue;
            }

            try {
                $route->reporter()->report($envelope, $decision);
                $reported[] = $route->name();
            } catch (Throwable $failure) {
                $failures[] = new FailureReporterFailure($route->name(), $failure);

                try {
                    $this->emergencyReporter->report($route->name(), $envelope, $decision, $failure);
                } catch (Throwable) {
                    // Terminal emergency reporting boundary. Never recurse through this dispatcher.
                }
            }
        }

        return new FailureReportingResult($reported, $filtered, $failures);
    }
}
