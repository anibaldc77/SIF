<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;

/**
 * Read-only contract for an immutable execution context.
 */
interface ExecutionContextInterface
{
    public function contextId(): ContextId;

    public function correlationId(): ContextId;

    public function causationId(): ?ContextId;

    public function parentContextId(): ?ContextId;

    public function actorId(): ?string;

    public function tenantId(): ?string;

    public function operation(): ?string;

    public function source(): ?string;

    public function locale(): ?string;

    public function timezone(): ?string;

    public function createdAt(): DateTimeImmutable;

    public function attributes(): ContextAttributes;
}
