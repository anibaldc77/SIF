<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Exceptions;

final class InvalidEnvironmentPrecedenceException extends EnvironmentException
{
    /**
     * @param list<string> $precedence
     */
    public static function forSources(array $precedence): self
    {
        return new self(sprintf(
            'Environment precedence must contain each source exactly once: env, server, process. Received: %s.',
            implode(', ', $precedence),
        ));
    }
}
