<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Routing;

use InvalidArgumentException;
use Sif\Foundation\Logging\Contracts\EmergencyLogReporterInterface;
use Sif\Foundation\Logging\LogRecord;
use Throwable;

final readonly class LogRouter
{
    /** @var list<LogRoute> */
    private array $routes;

    /** @param list<LogRoute> $routes */
    public function __construct(array $routes, private EmergencyLogReporterInterface $emergencyReporter)
    {
        if ($routes === []) {
            throw new InvalidArgumentException('At least one log route is required.');
        }

        $names = [];
        foreach ($routes as $route) {
            if (isset($names[$route->name()])) {
                throw new InvalidArgumentException(sprintf('Duplicate log route "%s".', $route->name()));
            }
            $names[$route->name()] = true;
        }
        $this->routes = array_values($routes);
    }

    public function dispatch(LogRecord $record): LogDispatchReport
    {
        $handled = [];
        $filtered = [];
        $failures = [];

        foreach ($this->routes as $route) {
            if (!$route->filter()->accepts($record)) {
                $filtered[] = $route->name();
                continue;
            }

            try {
                $route->handler()->handle($record);
                $handled[] = $route->name();
            } catch (Throwable $failure) {
                $failures[] = new LogHandlerFailure($route->name(), $failure);
                try {
                    $this->emergencyReporter->report($route->name(), $record, $failure);
                } catch (Throwable) {
                    // Emergency reporting is a terminal safety boundary and must not recurse.
                }
            }
        }

        return new LogDispatchReport($handled, $filtered, $failures);
    }
}
