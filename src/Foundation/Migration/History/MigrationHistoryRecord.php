<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\History;

use DateTimeImmutable;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryRecordException;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationVersion;

final readonly class MigrationHistoryRecord
{
    private ?string $batch;

    public function __construct(
        private MigrationId $id,
        private MigrationChecksum $checksum,
        private MigrationHistoryStatus $status,
        private DateTimeImmutable $recordedAt,
        private ?MigrationVersion $version = null,
        ?string $batch = null,
    ) {
        if ($batch !== null) {
            $batch = trim($batch);
            if ($batch === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $batch) !== 1) {
                throw new InvalidMigrationHistoryRecordException(
                    'Migration history batch must be a safe non-empty token.',
                );
            }
        }

        $this->batch = $batch;
    }

    public function id(): MigrationId
    {
        return $this->id;
    }

    public function checksum(): MigrationChecksum
    {
        return $this->checksum;
    }

    public function status(): MigrationHistoryStatus
    {
        return $this->status;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function version(): ?MigrationVersion
    {
        return $this->version;
    }

    public function batch(): ?string
    {
        return $this->batch;
    }

    /** @return array{id: string, checksum: string, status: string, recorded_at: string, version: string|null, batch: string|null} */
    public function summary(): array
    {
        return [
            'id' => $this->id->value(),
            'checksum' => $this->checksum->value(),
            'status' => $this->status->value(),
            'recorded_at' => $this->recordedAt->format(DATE_ATOM),
            'version' => $this->version?->value(),
            'batch' => $this->batch,
        ];
    }
}
