<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Declares explicit event-to-method subscriptions without reflection. */
interface EventSubscriberInterface
{
    /**
     * Each value is either a method name or a method name with priority.
     *
     * @return array<class-string, string|array{0: string, 1: int}>
     */
    public static function subscribedEvents(): array;
}
