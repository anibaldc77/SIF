<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Contracts\AuditChangeSetProviderInterface;
use Sif\Foundation\Contracts\AuditMetadataProviderInterface;
use Sif\Foundation\Contracts\AuditModelAdapterInterface;
use Sif\Foundation\Contracts\AuditableSubjectInterface;
use Sif\Foundation\Contracts\AuditSnapshotProviderInterface;
use Sif\Foundation\Exceptions\UnsupportedAuditableModelException;

final readonly class AuditModelDescriber
{
    public function describe(
        object $model,
        ?AuditModelAdapterInterface $adapter = null,
    ): AuditModelDescription {
        if ($adapter !== null) {
            return new AuditModelDescription(
                subject: $adapter->subject($model),
                metadata: $adapter->metadata($model),
                snapshot: $adapter->snapshot($model),
                changes: $model instanceof AuditChangeSetProviderInterface
                    ? $model->auditChanges()
                    : new AuditPayload(),
            );
        }

        if (!$model instanceof AuditableSubjectInterface) {
            throw new UnsupportedAuditableModelException(
                sprintf(
                    'Model of type "%s" does not provide an audit subject and no adapter was supplied.',
                    $model::class,
                ),
            );
        }

        return new AuditModelDescription(
            subject: $model->auditSubject(),
            metadata: $model instanceof AuditMetadataProviderInterface
                ? $model->auditMetadata()
                : new AuditPayload(),
            snapshot: $model instanceof AuditSnapshotProviderInterface
                ? $model->auditSnapshot()
                : new AuditPayload(),
            changes: $model instanceof AuditChangeSetProviderInterface
                ? $model->auditChanges()
                : new AuditPayload(),
        );
    }
}
