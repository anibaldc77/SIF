<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Installer\AuthorizedInstallationTarget;
use Sif\Foundation\Installer\Exceptions\DuplicateMutationDescriptorException;
use Sif\Foundation\Installer\Exceptions\InvalidAuthorizedInstallationTargetException;
use Sif\Foundation\Installer\Exceptions\InvalidMutationDescriptorException;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\OverwritePolicy;
use Sif\Foundation\Installer\RollbackPolicy;

final class SafeMutationPlanningTest extends TestCase
{
    public function testNormalizesSafeRelativeTarget(): void
    {
        $target = new AuthorizedInstallationTarget('Application', 'config\\app.php');
        self::assertSame(['root' => 'application', 'relative_path' => 'config/app.php'], $target->summary());
    }

    /** @dataProvider unsafePaths */
    public function testRejectsUnsafePaths(string $path): void
    {
        $this->expectException(InvalidAuthorizedInstallationTargetException::class);
        new AuthorizedInstallationTarget('application', $path);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafePaths(): iterable
    {
        yield 'parent traversal' => ['../secret'];
        yield 'embedded traversal' => ['config/../secret'];
        yield 'absolute unix' => ['/etc/passwd'];
        yield 'absolute windows' => ['C:/secret'];
        yield 'empty segment' => ['config//app.php'];
    }

    public function testFilesystemMutationRequiresAuthorizedTarget(): void
    {
        $this->expectException(InvalidMutationDescriptorException::class);
        $this->descriptor(null);
    }

    public function testConditionalOverwriteRequiresExpectedFingerprint(): void
    {
        $this->expectException(InvalidMutationDescriptorException::class);
        new MutationDescriptor('config.replace', 'replace-file', MutationClassification::filesystem(), new AuthorizedInstallationTarget('application', 'config/app.php'), OverwritePolicy::ifUnchanged(), RollbackPolicy::compensating());
    }

    public function testPlanFingerprintIsDeterministic(): void
    {
        $first = new MutationPlan([$this->descriptor(new AuthorizedInstallationTarget('application', 'config/app.php'))]);
        $second = new MutationPlan([$this->descriptor(new AuthorizedInstallationTarget('application', 'config/app.php'))]);
        self::assertSame($first->fingerprint(), $second->fingerprint());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $first->fingerprint());
    }

    public function testPlanFingerprintChangesWithOrderedIntent(): void
    {
        $a = $this->descriptor(new AuthorizedInstallationTarget('application', 'config/a.php'), 'a');
        $b = $this->descriptor(new AuthorizedInstallationTarget('application', 'config/b.php'), 'b');
        self::assertNotSame((new MutationPlan([$a, $b]))->fingerprint(), (new MutationPlan([$b, $a]))->fingerprint());
    }

    public function testDuplicateIdentifiersFail(): void
    {
        $mutation = $this->descriptor(new AuthorizedInstallationTarget('application', 'config/app.php'));
        $this->expectException(DuplicateMutationDescriptorException::class);
        new MutationPlan([$mutation, $mutation]);
    }

    public function testSummaryContainsFingerprintsInsteadOfPayloads(): void
    {
        $plan = new MutationPlan([$this->descriptor(new AuthorizedInstallationTarget('application', 'config/app.php'))]);
        $encoded = json_encode($plan->summary(), JSON_THROW_ON_ERROR);
        self::assertStringContainsString(hash('sha256', 'super-secret-value'), $encoded);
        self::assertStringNotContainsString('super-secret-value', $encoded);
    }

    private function descriptor(?AuthorizedInstallationTarget $target, string $identifier = 'config.create'): MutationDescriptor
    {
        return new MutationDescriptor(
            $identifier,
            'create-file',
            MutationClassification::filesystem(),
            $target,
            OverwritePolicy::deny(),
            RollbackPolicy::compensating(),
            hash('sha256', 'super-secret-value'),
            null,
            ['template' => 'application-config'],
        );
    }
}
