<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditModelDescriber;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Contracts\AuditChangeSetProviderInterface;
use Sif\Foundation\Contracts\AuditMetadataProviderInterface;
use Sif\Foundation\Contracts\AuditModelAdapterInterface;
use Sif\Foundation\Contracts\AuditableSubjectInterface;
use Sif\Foundation\Contracts\AuditSnapshotProviderInterface;
use Sif\Foundation\Exceptions\UnsupportedAuditableModelException;

final class AuditModelCustomizationTest extends TestCase
{
    public function testDescriberUsesExplicitModelContracts(): void
    {
        $model = new class implements
            AuditableSubjectInterface,
            AuditMetadataProviderInterface,
            AuditSnapshotProviderInterface,
            AuditChangeSetProviderInterface
        {
            public function auditSubject(): AuditSubject
            {
                return new AuditSubject('document', 'DOC-001');
            }

            public function auditMetadata(): AuditPayload
            {
                return new AuditPayload([
                    'module' => 'documents',
                    'classification' => 'legal',
                ]);
            }

            public function auditSnapshot(): AuditPayload
            {
                return new AuditPayload([
                    'status' => 'signed',
                    'version' => 3,
                ]);
            }

            public function auditChanges(): AuditPayload
            {
                return new AuditPayload([
                    'status' => [
                        'before' => 'draft',
                        'after' => 'signed',
                    ],
                ]);
            }
        };

        $description = (new AuditModelDescriber())->describe($model);

        self::assertSame('document', $description->subject()->type());
        self::assertSame('DOC-001', $description->subject()->identifier());
        self::assertSame('documents', $description->metadata()->get('module'));
        self::assertSame('signed', $description->snapshot()->get('status'));
        self::assertSame(
            'draft',
            $description->changes()->all()['status']['before'],
        );
    }

    public function testOptionalProvidersDefaultToEmptyPayloads(): void
    {
        $model = new class implements AuditableSubjectInterface
        {
            public function auditSubject(): AuditSubject
            {
                return new AuditSubject('system');
            }
        };

        $description = (new AuditModelDescriber())->describe($model);

        self::assertTrue($description->metadata()->isEmpty());
        self::assertTrue($description->snapshot()->isEmpty());
        self::assertTrue($description->changes()->isEmpty());
    }

    public function testAdapterSupportsExternalModelWithoutCoreContracts(): void
    {
        $model = new class
        {
            public string $id = 'CASE-100';

            public string $status = 'open';
        };

        $adapter = new class implements AuditModelAdapterInterface
        {
            public function subject(object $model): AuditSubject
            {
                /** @var object{id: string} $model */
                return new AuditSubject('case', $model->id);
            }

            public function metadata(object $model): AuditPayload
            {
                return new AuditPayload([
                    'source' => 'legacy-model',
                ]);
            }

            public function snapshot(object $model): AuditPayload
            {
                /** @var object{status: string} $model */
                return new AuditPayload([
                    'status' => $model->status,
                ]);
            }
        };

        $description = (new AuditModelDescriber())->describe($model, $adapter);

        self::assertSame('case', $description->subject()->type());
        self::assertSame('CASE-100', $description->subject()->identifier());
        self::assertSame('legacy-model', $description->metadata()->get('source'));
        self::assertSame('open', $description->snapshot()->get('status'));
        self::assertTrue($description->changes()->isEmpty());
    }

    public function testAdapterTakesPrecedenceOverModelSubjectContract(): void
    {
        $model = new class implements AuditableSubjectInterface
        {
            public function auditSubject(): AuditSubject
            {
                return new AuditSubject('native', 'N-1');
            }
        };

        $adapter = new class implements AuditModelAdapterInterface
        {
            public function subject(object $model): AuditSubject
            {
                return new AuditSubject('adapted', 'A-1');
            }

            public function metadata(object $model): AuditPayload
            {
                return new AuditPayload();
            }

            public function snapshot(object $model): AuditPayload
            {
                return new AuditPayload();
            }
        };

        $description = (new AuditModelDescriber())->describe($model, $adapter);

        self::assertSame('adapted', $description->subject()->type());
        self::assertSame('A-1', $description->subject()->identifier());
    }

    public function testAdapterStillAllowsExplicitChangeSetProvider(): void
    {
        $model = new class implements AuditChangeSetProviderInterface
        {
            public function auditChanges(): AuditPayload
            {
                return new AuditPayload([
                    'amount' => [
                        'before' => 100,
                        'after' => 150,
                    ],
                ]);
            }
        };

        $adapter = new class implements AuditModelAdapterInterface
        {
            public function subject(object $model): AuditSubject
            {
                return new AuditSubject('invoice', 'INV-1');
            }

            public function metadata(object $model): AuditPayload
            {
                return new AuditPayload();
            }

            public function snapshot(object $model): AuditPayload
            {
                return new AuditPayload(['amount' => 150]);
            }
        };

        $description = (new AuditModelDescriber())->describe($model, $adapter);

        self::assertSame(
            100,
            $description->changes()->all()['amount']['before'],
        );
        self::assertSame(
            150,
            $description->changes()->all()['amount']['after'],
        );
    }

    public function testUnsupportedModelFailsWithoutReflectionFallback(): void
    {
        $this->expectException(UnsupportedAuditableModelException::class);

        (new AuditModelDescriber())->describe(new \stdClass());
    }

    public function testDescriptionPreservesProvidedValueIdentity(): void
    {
        $subject = new AuditSubject('user', 'USR-1');
        $metadata = new AuditPayload(['role' => 'reviewer']);
        $snapshot = new AuditPayload(['active' => true]);
        $changes = new AuditPayload(['active' => ['before' => false, 'after' => true]]);

        $description = new \Sif\Foundation\Audit\AuditModelDescription(
            $subject,
            $metadata,
            $snapshot,
            $changes,
        );

        self::assertSame($subject, $description->subject());
        self::assertSame($metadata, $description->metadata());
        self::assertSame($snapshot, $description->snapshot());
        self::assertSame($changes, $description->changes());
    }
}
