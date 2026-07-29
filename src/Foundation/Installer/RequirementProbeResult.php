<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidRequirementProbeResultException;

final readonly class RequirementProbeResult
{
    private const MAX_MESSAGE_BYTES = 2048;

    private string $message;

    public function __construct(
        private RequirementIdentifier $identifier,
        private RequirementSeverity $severity,
        private RequirementStatus $status,
        string $message,
    ) {
        $message = trim($message);

        if ($message === '' || strlen($message) > self::MAX_MESSAGE_BYTES) {
            throw new InvalidRequirementProbeResultException(
                'Requirement result message must contain between 1 and 2048 bytes.',
            );
        }

        $this->message = $message;
    }

    public static function passed(
        RequirementIdentifier $identifier,
        RequirementSeverity $severity,
        string $message,
    ): self {
        return new self($identifier, $severity, RequirementStatus::Passed, $message);
    }

    public static function failed(
        RequirementIdentifier $identifier,
        RequirementSeverity $severity,
        string $message,
    ): self {
        return new self($identifier, $severity, RequirementStatus::Failed, $message);
    }

    public function identifier(): RequirementIdentifier
    {
        return $this->identifier;
    }

    public function severity(): RequirementSeverity
    {
        return $this->severity;
    }

    public function status(): RequirementStatus
    {
        return $this->status;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function passedRequirement(): bool
    {
        return $this->status === RequirementStatus::Passed;
    }

    /**
     * @return array{identifier: string, severity: string, status: string, message: string}
     */
    public function summary(): array
    {
        return [
            'identifier' => $this->identifier->value(),
            'severity' => $this->severity->value,
            'status' => $this->status->value,
            'message' => $this->message,
        ];
    }
}
