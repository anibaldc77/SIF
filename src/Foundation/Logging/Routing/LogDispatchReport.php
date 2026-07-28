<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Routing;

final readonly class LogDispatchReport
{
    /** @var list<string> */
    private array $handledRoutes;
    /** @var list<string> */
    private array $filteredRoutes;
    /** @var list<LogHandlerFailure> */
    private array $failures;

    /**
     * @param list<string> $handledRoutes
     * @param list<string> $filteredRoutes
     * @param list<LogHandlerFailure> $failures
     */
    public function __construct(array $handledRoutes, array $filteredRoutes, array $failures)
    {
        $this->handledRoutes = array_values($handledRoutes);
        $this->filteredRoutes = array_values($filteredRoutes);
        $this->failures = array_values($failures);
    }

    /** @return list<string> */
    public function handledRoutes(): array { return $this->handledRoutes; }
    /** @return list<string> */
    public function filteredRoutes(): array { return $this->filteredRoutes; }
    /** @return list<LogHandlerFailure> */
    public function failures(): array { return $this->failures; }
    public function handledCount(): int { return count($this->handledRoutes); }
    public function failureCount(): int { return count($this->failures); }
    public function succeeded(): bool { return $this->failures === []; }
}
