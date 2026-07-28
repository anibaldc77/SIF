<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Filtering;

use InvalidArgumentException;
use Sif\Foundation\Logging\Contracts\LogRecordFilterInterface;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogRecord;

final readonly class ChannelLogRecordFilter implements LogRecordFilterInterface
{
    /** @var list<string> */
    private array $channels;

    /** @param list<LogChannel> $channels */
    public function __construct(array $channels)
    {
        if ($channels === []) {
            throw new InvalidArgumentException('At least one log channel is required.');
        }

        $values = [];
        foreach ($channels as $channel) {
            $values[] = $channel->value();
        }

        $this->channels = array_values(array_unique($values));
    }

    public function accepts(LogRecord $record): bool
    {
        return in_array($record->channel()->value(), $this->channels, true);
    }
}
