<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Resolves listeners applicable to a dispatched event. */
interface ListenerProviderInterface
{
    /**
     * @return iterable<callable(object): void>
     */
    public function listenersFor(object $event): iterable;
}
