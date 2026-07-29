<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationIdentifierException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationModeException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationOptionException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationRequestException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationStepIdentifierException;
use Sif\Foundation\Installer\Exceptions\InvalidMutationClassificationException;
use Sif\Foundation\Installer\Exceptions\InvalidRequirementIdentifierException;
use Sif\Foundation\Installer\Exceptions\InvalidRollbackPolicyException;
use Sif\Foundation\Installer\Exceptions\InvalidStepDependencyException;
use Sif\Foundation\Installer\InstallationIdentifier;
use Sif\Foundation\Installer\InstallationMode;
use Sif\Foundation\Installer\InstallationOption;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\InstallationStepIdentifier;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\RequirementIdentifier;
use Sif\Foundation\Installer\RollbackPolicy;
use Sif\Foundation\Installer\StepDependency;

final class InstallationValueModelTest extends TestCase
{
    public function testInstallationIdentifierIsTrimmedAndCaseSensitive(): void
    {
        $identifier = new InstallationIdentifier(' App.Install ');
        self::assertSame('App.Install', $identifier->value());
        self::assertFalse($identifier->equals(new InstallationIdentifier('app.install')));
    }

    public function testInstallationIdentifierRejectsWhitespace(): void
    {
        $this->expectException(InvalidInstallationIdentifierException::class);
        new InstallationIdentifier('app install');
    }

    public function testModeProvidesKnownFactoriesAndSafeExtensions(): void
    {
        self::assertTrue(InstallationMode::fresh()->isFresh());
        self::assertSame('repair', InstallationMode::repair()->value());
        self::assertSame('upgrade', InstallationMode::upgrade()->value());
        self::assertSame('tenant-bootstrap', (new InstallationMode(' Tenant-Bootstrap '))->value());
    }

    public function testModeRejectsUnsafeVocabulary(): void
    {
        $this->expectException(InvalidInstallationModeException::class);
        new InstallationMode('fresh/install');
    }

    public function testOptionNormalizesNameAndRedactsSensitiveValue(): void
    {
        $option = new InstallationOption(' Database.Password ', 'secret', true);

        self::assertSame('database.password', $option->name());
        self::assertSame('secret', $option->value());
        self::assertSame('[REDACTED]', $option->diagnosticValue());
        self::assertSame([
            'name' => 'database.password',
            'value' => '[REDACTED]',
            'sensitive' => true,
        ], $option->summary());
    }

    public function testOptionRejectsUnsafeName(): void
    {
        $this->expectException(InvalidInstallationOptionException::class);
        new InstallationOption('database/password', 'secret');
    }

    public function testOptionRejectsNonFiniteNumber(): void
    {
        $this->expectException(InvalidInstallationOptionException::class);
        new InstallationOption('ratio', INF);
    }

    public function testRequestPreservesOrderAndProducesSecretSafeSummary(): void
    {
        $request = new InstallationRequest(
            new InstallationIdentifier('application'),
            InstallationMode::fresh(),
            [
                new InstallationOption('locale', 'es-AR'),
                new InstallationOption('database.password', 'secret', true),
            ],
        );

        self::assertSame('application', $request->identifier()->value());
        self::assertSame('fresh', $request->mode()->value());
        self::assertTrue($request->hasOption(' LOCALE '));
        self::assertSame('secret', $request->option('database.password')?->value());
        self::assertSame([
            'identifier' => 'application',
            'mode' => 'fresh',
            'options' => [
                [
                    'name' => 'locale',
                    'value' => 'es-AR',
                    'sensitive' => false,
                ],
                [
                    'name' => 'database.password',
                    'value' => '[REDACTED]',
                    'sensitive' => true,
                ],
            ],
        ], $request->summary());
    }

    public function testRequestRejectsDuplicateOptionNamesAfterNormalization(): void
    {
        $this->expectException(InvalidInstallationRequestException::class);

        new InstallationRequest(
            new InstallationIdentifier('application'),
            InstallationMode::fresh(),
            [
                new InstallationOption('locale', 'es-AR'),
                new InstallationOption(' LOCALE ', 'en-US'),
            ],
        );
    }

    public function testRequestRejectsUntypedIterableMembers(): void
    {
        $this->expectException(InvalidInstallationRequestException::class);

        // This intentionally violates the documented generic type to verify
        // the constructor's defensive runtime validation at an untyped boundary.
        new InstallationRequest(
            new InstallationIdentifier('application'),
            InstallationMode::fresh(),
            // @phpstan-ignore-next-line
            [new \stdClass()],
        );
    }

    public function testRequirementIdentifierRejectsPathSyntax(): void
    {
        $this->expectException(InvalidRequirementIdentifierException::class);
        new RequirementIdentifier('../php');
    }

    public function testStepIdentifierRejectsBlankValue(): void
    {
        $this->expectException(InvalidInstallationStepIdentifierException::class);
        new InstallationStepIdentifier('   ');
    }

    public function testStepDependencyExposesRequiredAndOptionalForms(): void
    {
        $identifier = new InstallationStepIdentifier('configuration.write');

        self::assertSame([
            'step' => 'configuration.write',
            'required' => true,
        ], StepDependency::required($identifier)->summary());

        self::assertFalse(
            StepDependency::optional($identifier)->requiredDependency(),
        );
    }

    public function testStepDependencyRejectsSelfDependencyWhenOwnerIsKnown(): void
    {
        $identifier = new InstallationStepIdentifier('configuration.write');

        $this->expectException(InvalidStepDependencyException::class);
        StepDependency::required($identifier)->assertNotSelfDependency($identifier);
    }

    public function testMutationClassificationProvidesKnownVocabularyAndExtensions(): void
    {
        self::assertFalse(MutationClassification::none()->mutatesState());
        self::assertTrue(MutationClassification::filesystem()->mutatesState());
        self::assertSame('configuration', MutationClassification::configuration()->value());
        self::assertSame('secret-reference', MutationClassification::secretReference()->value());
        self::assertSame('infrastructure', MutationClassification::infrastructure()->value());
        self::assertSame('migration', MutationClassification::migration()->value());
        self::assertSame('cache-warmup', (new MutationClassification(' Cache-Warmup '))->value());
    }

    public function testMutationClassificationRejectsUnsafeVocabulary(): void
    {
        $this->expectException(InvalidMutationClassificationException::class);
        new MutationClassification('filesystem/write');
    }

    public function testRollbackPolicyExposesCapabilityWithoutClaimingTransactions(): void
    {
        self::assertFalse(RollbackPolicy::unsupported()->isSupported());
        self::assertTrue(RollbackPolicy::compensating()->isSupported());
        self::assertTrue(RollbackPolicy::required()->isRequired());
        self::assertSame('snapshot-restore', (new RollbackPolicy(' Snapshot-Restore '))->value());
    }

    public function testRollbackPolicyRejectsUnsafeVocabulary(): void
    {
        $this->expectException(InvalidRollbackPolicyException::class);
        new RollbackPolicy('required/always');
    }
}
