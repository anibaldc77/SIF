<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Planning;

use Sif\Foundation\Logging\Contracts\LogRecordFactoryInterface;
use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\Processing\LogRecordProcessorPipeline;
use Sif\Foundation\Logging\Routing\LogRouter;

final readonly class LoggingPlan
{
    public function __construct(
        private LogRecordFactoryInterface $recordFactory,
        private LogRouter $router,
        private LogChannel $defaultChannel,
        ?LogRecordProcessorInterface $processor = null,
    ) {
        $this->processor = $processor ?? new LogRecordProcessorPipeline();
    }

    private LogRecordProcessorInterface $processor;

    public function recordFactory(): LogRecordFactoryInterface
    {
        return $this->recordFactory;
    }

    public function processor(): LogRecordProcessorInterface
    {
        return $this->processor;
    }

    public function router(): LogRouter
    {
        return $this->router;
    }

    public function defaultChannel(): LogChannel
    {
        return $this->defaultChannel;
    }

    public function withDefaultChannel(LogChannel $channel): self
    {
        return new self($this->recordFactory, $this->router, $channel, $this->processor);
    }

    public function withProcessor(LogRecordProcessorInterface $processor): self
    {
        return new self($this->recordFactory, $this->router, $this->defaultChannel, $processor);
    }
}
