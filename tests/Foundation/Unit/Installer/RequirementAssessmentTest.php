<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Installer\Contracts\RequirementProbeInterface;
use Sif\Foundation\Installer\Exceptions\DuplicateRequirementProbeException;
use Sif\Foundation\Installer\Exceptions\InvalidRequirementAssessmentReportException;
use Sif\Foundation\Installer\Exceptions\InvalidRequirementProbeException;
use Sif\Foundation\Installer\Exceptions\InvalidRequirementProbeResultException;
use Sif\Foundation\Installer\Exceptions\RequirementProbeExecutionException;
use Sif\Foundation\Installer\InstallationIdentifier;
use Sif\Foundation\Installer\InstallationMode;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\RequirementAssessmentReport;
use Sif\Foundation\Installer\RequirementIdentifier;
use Sif\Foundation\Installer\RequirementProbeResult;
use Sif\Foundation\Installer\RequirementSeverity;
use Sif\Foundation\Installer\Requirements\RequirementAssessor;

final class RequirementAssessmentTest extends TestCase
{
    public function testRequiredFailurePreventsProceeding(): void
    {
        $report = new RequirementAssessmentReport([
            RequirementProbeResult::failed(
                new RequirementIdentifier('php.version'),
                RequirementSeverity::Required,
                'PHP version is not supported.',
            ),
        ]);

        self::assertFalse($report->canProceed());
        self::assertFalse($report->hasWarnings());
    }

    public function testOptionalFailureProducesWarningWithoutBlocking(): void
    {
        $report = new RequirementAssessmentReport([
            RequirementProbeResult::failed(
                new RequirementIdentifier('extension.intl'),
                RequirementSeverity::Optional,
                'The intl extension is unavailable.',
            ),
        ]);

        self::assertTrue($report->canProceed());
        self::assertTrue($report->hasWarnings());
    }

    public function testReportSummaryIsDeterministic(): void
    {
        $result = RequirementProbeResult::passed(
            new RequirementIdentifier('php.version'),
            RequirementSeverity::Required,
            'PHP version is supported.',
        );

        self::assertSame([
            [
                'identifier' => 'php.version',
                'severity' => 'required',
                'status' => 'passed',
                'message' => 'PHP version is supported.',
            ],
        ], (new RequirementAssessmentReport([$result]))->summary());
    }

    public function testResultRejectsBlankMessage(): void
    {
        $this->expectException(InvalidRequirementProbeResultException::class);

        RequirementProbeResult::passed(
            new RequirementIdentifier('php.version'),
            RequirementSeverity::Required,
            '   ',
        );
    }

    public function testReportRejectsDuplicateIdentifiers(): void
    {
        $identifier = new RequirementIdentifier('php.version');

        $this->expectException(InvalidRequirementAssessmentReportException::class);

        new RequirementAssessmentReport([
            RequirementProbeResult::passed($identifier, RequirementSeverity::Required, 'Supported.'),
            RequirementProbeResult::failed($identifier, RequirementSeverity::Required, 'Unsupported.'),
        ]);
    }

    public function testAssessorOrdersByPriorityAndThenRegistrationOrder(): void
    {
        $assessor = new RequirementAssessor();
        $report = $assessor->assess($this->request(), [
            $this->probe('third', RequirementSeverity::Optional, 20, true),
            $this->probe('first', RequirementSeverity::Required, 10, true),
            $this->probe('second', RequirementSeverity::Required, 10, true),
        ]);

        self::assertSame(
            ['first', 'second', 'third'],
            array_map(
                static fn (RequirementProbeResult $result): string => $result->identifier()->value(),
                $report->results(),
            ),
        );
    }

    public function testAssessorRejectsDuplicateProbeIdentifiers(): void
    {
        $this->expectException(DuplicateRequirementProbeException::class);

        (new RequirementAssessor())->assess($this->request(), [
            $this->probe('php.version', RequirementSeverity::Required, 10, true),
            $this->probe('php.version', RequirementSeverity::Optional, 20, true),
        ]);
    }

    public function testAssessorRejectsUntypedMembers(): void
    {
        $this->expectException(InvalidRequirementProbeException::class);

        // @phpstan-ignore-next-line
        (new RequirementAssessor())->assess($this->request(), [new \stdClass()]);
    }

    public function testAssessorRejectsMismatchedResultIdentifier(): void
    {
        $probe = new class implements RequirementProbeInterface {
            public function identifier(): RequirementIdentifier
            {
                return new RequirementIdentifier('php.version');
            }

            public function severity(): RequirementSeverity
            {
                return RequirementSeverity::Required;
            }

            public function priority(): int
            {
                return 10;
            }

            public function probe(InstallationRequest $request): RequirementProbeResult
            {
                return RequirementProbeResult::passed(
                    new RequirementIdentifier('extension.json'),
                    RequirementSeverity::Required,
                    'Available.',
                );
            }
        };

        $this->expectException(InvalidRequirementProbeException::class);
        (new RequirementAssessor())->assess($this->request(), [$probe]);
    }

    public function testAssessorRejectsMismatchedResultSeverity(): void
    {
        $probe = new class implements RequirementProbeInterface {
            public function identifier(): RequirementIdentifier
            {
                return new RequirementIdentifier('php.version');
            }

            public function severity(): RequirementSeverity
            {
                return RequirementSeverity::Required;
            }

            public function priority(): int
            {
                return 10;
            }

            public function probe(InstallationRequest $request): RequirementProbeResult
            {
                return RequirementProbeResult::passed(
                    $this->identifier(),
                    RequirementSeverity::Optional,
                    'Available.',
                );
            }
        };

        $this->expectException(InvalidRequirementProbeException::class);
        (new RequirementAssessor())->assess($this->request(), [$probe]);
    }

    public function testAssessorPreservesProbeFailureAsCause(): void
    {
        $cause = new \RuntimeException('read failure');
        $probe = new class($cause) implements RequirementProbeInterface {
            public function __construct(private readonly \Throwable $cause)
            {
            }

            public function identifier(): RequirementIdentifier
            {
                return new RequirementIdentifier('filesystem.readable');
            }

            public function severity(): RequirementSeverity
            {
                return RequirementSeverity::Required;
            }

            public function priority(): int
            {
                return 10;
            }

            public function probe(InstallationRequest $request): RequirementProbeResult
            {
                throw $this->cause;
            }
        };

        try {
            (new RequirementAssessor())->assess($this->request(), [$probe]);
            self::fail('Expected probe execution exception.');
        } catch (RequirementProbeExecutionException $exception) {
            self::assertSame($cause, $exception->getPrevious());
            self::assertStringContainsString('filesystem.readable', $exception->getMessage());
        }
    }

    private function request(): InstallationRequest
    {
        return new InstallationRequest(
            new InstallationIdentifier('application'),
            InstallationMode::fresh(),
            [],
        );
    }

    private function probe(
        string $identifier,
        RequirementSeverity $severity,
        int $priority,
        bool $passes,
    ): RequirementProbeInterface {
        return new class($identifier, $severity, $priority, $passes) implements RequirementProbeInterface {
            public function __construct(
                private readonly string $probeIdentifier,
                private readonly RequirementSeverity $probeSeverity,
                private readonly int $probePriority,
                private readonly bool $passes,
            ) {
            }

            public function identifier(): RequirementIdentifier
            {
                return new RequirementIdentifier($this->probeIdentifier);
            }

            public function severity(): RequirementSeverity
            {
                return $this->probeSeverity;
            }

            public function priority(): int
            {
                return $this->probePriority;
            }

            public function probe(InstallationRequest $request): RequirementProbeResult
            {
                return $this->passes
                    ? RequirementProbeResult::passed($this->identifier(), $this->severity(), 'Requirement passed.')
                    : RequirementProbeResult::failed($this->identifier(), $this->severity(), 'Requirement failed.');
            }
        };
    }
}
