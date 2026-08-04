<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectEntryPoint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Template\SessionSecurityTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\Cli\Extension\SessionSecurityCommandContributor;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Http\Cookie\CookieSameSite;
use Sif\Foundation\Security\Csrf\CsrfConfiguration;
use Sif\Foundation\Session\Policy\SessionRegenerationPolicy;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\Transport\SessionCookieConfiguration;

final class SessionSecurityCliSkeletonIntegrationTest extends TestCase
{
    public function testContributorPublishesSafeInspectionCommands(): void
    {
        $contributor = new SessionSecurityCommandContributor(
            new SessionCookieConfiguration(
                name: '__Host-sif_session',
                sameSite: CookieSameSite::Lax,
            ),
            new SessionPolicy(28800, 1800),
            new SessionRegenerationPolicy(900),
            new CsrfConfiguration(),
        );

        $commands = $contributor->commands();
        self::assertSame(['csrf:config', 'session:config'], array_map(
            static fn ($command): string => $command->metadata()->name()->value(),
            $commands,
        ));

        $sessionData = $commands[1]->execute(new CliInvocation(new CliCommandName('session:config')))->data();
        self::assertSame(900, $sessionData['regeneration_interval']);
        self::assertArrayNotHasKey('session_id', $sessionData);
        self::assertArrayNotHasKey('token', $commands[0]->execute(new CliInvocation(new CliCommandName('csrf:config')))->data());
    }

    public function testSkeletonArtifactsAreDeterministicAndUserOwned(): void
    {
        $paths = [
            'public/index.php',
            'config/session.php',
            'config/csrf.php',
            'app/Providers/SessionSecurityServiceProvider.php',
            'routes/web.php',
            'app/Controllers/WebFormController.php',
            'tests/Feature/WebFormCsrfTest.php',
        ];
        $definitions = array_map(
            static fn (string $path): ProjectPathDefinition => new ProjectPathDefinition(
                new ProjectPath($path),
                SkeletonOwnership::UserOwned,
            ),
            $paths,
        );

        $manifest = new ProjectManifest(
            new ProjectIdentifier('session-security-example'),
            'Session Security Example',
            new ProjectNamespace('Example\\SessionSecurity'),
            '1.0.0',
            '1.0.0',
            '^2.0',
            '8.2.0',
            [new ProjectEntryPoint('http', new ProjectPath('public/index.php'))],
            ['local'],
            $definitions,
            ['session.security'],
        );

        $factory = new SessionSecurityTemplateFactory();
        $first = $factory->artifacts($manifest);
        $second = $factory->artifacts($manifest);

        self::assertCount(6, $first);
        self::assertSame(
            array_map(static fn ($artifact): array => $artifact->summary(), $first),
            array_map(static fn ($artifact): array => $artifact->summary(), $second),
        );
        self::assertStringContainsString("'secure' => true", (string) $first[0]->content());
        self::assertStringContainsString('name="_csrf"', (string) $first[4]->content());
        self::assertStringContainsString("['session', 'csrf']", (string) $first[3]->content());
    }
}
