<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationChecksumException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationDescriptorException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationDirectionException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionModeException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationIdException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationRequestException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationVersionException;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\MigrationVersion;

final class MigrationValueModelTest extends TestCase
{
    public function testMigrationIdIsTrimmedAndCaseSensitive(): void
    {
        $id = new MigrationId(' 20260730:Create.Users ');
        self::assertSame('20260730:Create.Users', $id->value());
        self::assertFalse($id->equals(new MigrationId('20260730:create.users')));
    }

    public function testMigrationIdRejectsPathSyntax(): void
    {
        $this->expectException(InvalidMigrationIdException::class);
        new MigrationId('../create-users');
    }

    public function testVersionAcceptsTimestampAndSemanticForms(): void
    {
        self::assertSame('20260730083000', (new MigrationVersion(' 20260730083000 '))->value());
        self::assertSame('2.1.0-alpha+1', (new MigrationVersion('2.1.0-alpha+1'))->value());
    }

    public function testVersionRejectsWhitespace(): void
    {
        $this->expectException(InvalidMigrationVersionException::class);
        new MigrationVersion('version one');
    }

    public function testChecksumSupportsCanonicalSha256AndParsing(): void
    {
        $checksum = MigrationChecksum::sha256('canonical-content');
        self::assertSame('sha256', $checksum->algorithm());
        self::assertSame(64, strlen($checksum->digest()));
        self::assertTrue($checksum->equals(MigrationChecksum::parse($checksum->value())));
    }

    public function testChecksumRejectsMalformedRepresentation(): void
    {
        $this->expectException(InvalidMigrationChecksumException::class);
        MigrationChecksum::parse('sha256-without-digest');
    }

    public function testDirectionProvidesOnlyUpAndDown(): void
    {
        self::assertTrue(MigrationDirection::up()->isUp());
        self::assertTrue(MigrationDirection::down()->isDown());

        $this->expectException(InvalidMigrationDirectionException::class);
        new MigrationDirection('sideways');
    }

    public function testExecutionModeSeparatesPlanningFromMutation(): void
    {
        self::assertFalse(MigrationExecutionMode::dryRun()->mutatesState());
        self::assertTrue(MigrationExecutionMode::apply()->mutatesState());

        $this->expectException(InvalidMigrationExecutionModeException::class);
        new MigrationExecutionMode('automatic');
    }

    public function testDescriptorPreservesGovernedMetadata(): void
    {
        $descriptor = new MigrationDescriptor(
            new MigrationId('users.add-email'),
            MigrationChecksum::sha256('users.add-email:v1'),
            new MigrationVersion('20260730090000'),
            [new MigrationId('users.create')],
            true,
            ['Core', 'Identity'],
            'module.identity',
        );

        self::assertSame([
            'id' => 'users.add-email',
            'version' => '20260730090000',
            'checksum' => MigrationChecksum::sha256('users.add-email:v1')->value(),
            'dependencies' => ['users.create'],
            'reversible' => true,
            'tags' => ['core', 'identity'],
            'owner' => 'module.identity',
        ], $descriptor->summary());
    }

    public function testDescriptorRejectsSelfDependency(): void
    {
        $id = new MigrationId('users.create');
        $this->expectException(InvalidMigrationDescriptorException::class);
        new MigrationDescriptor($id, MigrationChecksum::sha256('content'), null, [$id]);
    }

    public function testDescriptorRejectsDuplicateDependency(): void
    {
        $dependency = new MigrationId('foundation.prepare');
        $this->expectException(InvalidMigrationDescriptorException::class);
        new MigrationDescriptor(
            new MigrationId('users.create'),
            MigrationChecksum::sha256('content'),
            null,
            [$dependency, new MigrationId('foundation.prepare')],
        );
    }

    public function testDescriptorRejectsUntypedDependencies(): void
    {
        $this->expectException(InvalidMigrationDescriptorException::class);
        new MigrationDescriptor(
            new MigrationId('users.create'),
            MigrationChecksum::sha256('content'),
            null,
            // @phpstan-ignore-next-line
            [new \stdClass()],
        );
    }

    public function testRequestProducesDeterministicSummary(): void
    {
        $request = new MigrationRequest(
            MigrationDirection::up(),
            MigrationExecutionMode::dryRun(),
            new MigrationId('users.add-email'),
            3,
            ['Identity', 'Core'],
        );

        self::assertSame([
            'direction' => 'up',
            'mode' => 'dry-run',
            'target' => 'users.add-email',
            'limit' => 3,
            'tags' => ['identity', 'core'],
        ], $request->summary());
    }

    public function testRequestRejectsInvalidLimit(): void
    {
        $this->expectException(InvalidMigrationRequestException::class);
        new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::apply(), null, 0);
    }

    public function testRequestRejectsDuplicateNormalizedTags(): void
    {
        $this->expectException(InvalidMigrationRequestException::class);
        new MigrationRequest(
            MigrationDirection::up(),
            MigrationExecutionMode::dryRun(),
            null,
            null,
            ['core', ' CORE '],
        );
    }
}
