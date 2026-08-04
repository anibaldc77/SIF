<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Template;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifact;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectPathDefinition;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;

final readonly class SessionSecurityTemplateFactory
{
    /** @return list<SkeletonArtifact> */
    public function artifacts(ProjectManifest $manifest): array
    {
        return [
            $this->file($this->requireUserOwnedPath($manifest, 'config/session.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

return [
    'cookie' => [
        'name' => '__Host-sif_session',
        'secure' => true,
        'http_only' => true,
        'same_site' => 'Lax',
        'path' => '/',
        'domain' => null,
    ],
    'absolute_lifetime' => 28800,
    'idle_lifetime' => 1800,
    'regeneration_interval' => 900,
];
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'config/csrf.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

return [
    'header_name' => 'X-CSRF-Token',
    'body_field' => '_csrf',
    'protected_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'app/Providers/SessionSecurityServiceProvider.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

namespace App\Providers;

final class SessionSecurityServiceProvider
{
    public function register(): void
    {
        // Bind the session store, session runtime, cookie transport and CSRF manager explicitly.
    }

    public function middleware(): array
    {
        return ['session', 'csrf'];
    }
}
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'routes/web.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

return [
    ['name' => 'form.show', 'method' => 'GET', 'path' => '/form', 'handler' => 'web.form.show', 'middleware' => ['session']],
    ['name' => 'form.submit', 'method' => 'POST', 'path' => '/form', 'handler' => 'web.form.submit', 'middleware' => ['session', 'csrf']],
];
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'app/Controllers/WebFormController.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Sif\Foundation\Security\Csrf\CsrfTokenManager;
use Sif\Foundation\Session\SessionState;

final readonly class WebFormController
{
    public function __construct(private CsrfTokenManager $csrf) {}

    public function show(SessionState $session): string
    {
        $token = $this->csrf->token($session)->value();
        $notice = $session->flashGet('notice');

        return sprintf(
            '<form method="post" action="/form"><input type="hidden" name="_csrf" value="%s"><button type="submit">Save</button></form>%s',
            htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            is_string($notice) ? htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '',
        );
    }

    public function submit(SessionState $session): array
    {
        $session->flash('notice', 'Saved successfully.');

        return ['status' => 'accepted'];
    }
}
PHPFILE
            ),
            $this->file($this->requireUserOwnedPath($manifest, 'tests/Feature/WebFormCsrfTest.php'), <<<'PHPFILE'
<?php

declare(strict_types=1);

namespace Tests\Feature;

final class WebFormCsrfTest
{
    public function documentsExpectedFlow(): array
    {
        return [
            'GET /form creates a session-bound token',
            'POST /form accepts the submitted _csrf value',
            'POST /form without a valid token returns 403',
            'successful submission stores flash data for the next request',
        ];
    }
}
PHPFILE
            ),
        ];
    }

    private function file(ProjectPathDefinition $definition, string $content): SkeletonArtifact
    {
        return new SkeletonArtifact($definition, SkeletonArtifactType::File, $content . "\n");
    }

    private function requireUserOwnedPath(ProjectManifest $manifest, string $path): ProjectPathDefinition
    {
        $definition = $manifest->paths()[$path] ?? null;
        if (!$definition instanceof ProjectPathDefinition) {
            throw new InvalidSkeletonValueException(sprintf('Session security path "%s" is not declared.', $path));
        }
        if ($definition->ownership() !== SkeletonOwnership::UserOwned) {
            throw new InvalidSkeletonValueException(sprintf('Session security path "%s" must be user-owned.', $path));
        }
        if ($definition->overwritePolicy() !== SkeletonOverwritePolicy::Fail) {
            throw new InvalidSkeletonValueException(sprintf('Session security path "%s" must use fail overwrite policy.', $path));
        }

        return $definition;
    }
}
