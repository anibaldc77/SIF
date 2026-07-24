<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Event;

use Sif\Foundation\Contracts\EventSubscriberInterface;

final class RecordingSubscriber implements EventSubscriberInterface
{
    /** @var list<string> */
    public array $log = [];

    public static function subscribedEvents(): array
    {
        return [
            ParentEvent::class => ['onParent', 20],
            MarkerEvent::class => 'onMarker',
        ];
    }

    public function onParent(object $event): void
    {
        $this->log[] = 'parent:' . $event::class;
    }

    public function onMarker(object $event): void
    {
        $this->log[] = 'marker:' . $event::class;
    }
}
