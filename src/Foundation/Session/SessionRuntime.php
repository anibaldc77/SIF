<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;
use Sif\Foundation\Session\Contracts\SessionStoreInterface;
use Sif\Foundation\Session\Exceptions\SessionException;
use Sif\Foundation\Session\Policy\SessionRegenerationPolicy;

final readonly class SessionRuntime
{
    public function __construct(
        private SessionStoreInterface $store,
        private SessionIdGeneratorInterface $idGenerator,
        private ClockInterface $clock,
        private SessionPolicy $policy = new SessionPolicy(),
        private SessionRegenerationPolicy $regenerationPolicy = new SessionRegenerationPolicy(),
    ) {
    }

    public function open(?string $candidateIdentifier): SessionOpenResult
    {
        $now = $this->clock->now();
        $accepted = false;
        $expired = false;
        $record = null;

        if ($candidateIdentifier !== null) {
            try {
                $id = new SessionId($candidateIdentifier);
                $record = $this->store->read($id);
                $accepted = $record !== null;
            } catch (SessionException) {
                $record = null;
            }
        }

        if ($record !== null && $record->expiredAt($now, $this->policy)) {
            $this->store->delete($record->id());
            $record = null;
            $accepted = false;
            $expired = true;
        }

        if ($record === null) {
            return new SessionOpenResult(
                new SessionState($this->idGenerator->generate(), [], $now, $now, new: true),
                $accepted,
                $expired,
            );
        }

        return new SessionOpenResult(
            new SessionState(
                $record->id(),
                $record->data(),
                $record->createdAt(),
                $record->lastAccessedAt(),
                $record->version(),
                false,
                $record->flashData(),
                $record->lastRegeneratedAt(),
            ),
            true,
            false,
        );
    }

    public function commit(SessionState $state): void
    {
        if ($state->destroyed()) {
            $this->store->delete($state->id());
            return;
        }

        $now = $this->clock->now();
        if ($this->regenerationPolicy->shouldRegenerate($state, $now)) {
            $previous = $state->regenerate($this->idGenerator->generate(), $now);
            $this->store->delete($previous);
        }

        $state->touch($now);
        $this->store->write($state->toRecord());
    }
}
