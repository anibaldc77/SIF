<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Resources\Exceptions\DuplicateResourcePublicationException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceContentFingerprintException;
use Sif\Foundation\Resources\Exceptions\InvalidResourcePublicationOrderException;
use Sif\Foundation\Resources\Exceptions\InvalidResourcePublicationRequestException;
use Sif\Foundation\Resources\Exceptions\ResourcePublicationTargetCollisionException;
use Sif\Foundation\Resources\Publication\DeterministicResourcePublicationPlanner;
use Sif\Foundation\Resources\Publication\PlannedResourcePublication;
use Sif\Foundation\Resources\Publication\ResourceContentFingerprint;
use Sif\Foundation\Resources\Publication\ResourcePublicationRequest;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourcePriority;
use Sif\Foundation\Resources\ResourceRootIdentifier;
use Sif\Foundation\Resources\ResourceType;

final class ResourcePublicationPlanningTest extends TestCase
{
    public function test_plan_is_ordered_by_target_path(): void
    {
        $plan = (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('zeta', 'js/zeta.js', 'zeta'),
            $this->request('alpha', 'css/alpha.css', 'alpha'),
        ]);

        self::assertSame(
            ['css/alpha.css', 'js/zeta.js'],
            array_map(static fn (PlannedResourcePublication $item): string => $item->request()->targetPath()->value(), $plan->publications()),
        );
        self::assertSame(2, $plan->count());
    }

    public function test_original_publication_order_is_preserved_as_provenance(): void
    {
        $plan = (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('second', 'z.js', 'z'),
            $this->request('first', 'a.js', 'a'),
        ]);

        self::assertSame(1, $plan->publications()[0]->publicationOrder());
        self::assertSame(0, $plan->publications()[1]->publicationOrder());
    }

    public function test_duplicate_resource_identity_is_rejected(): void
    {
        $this->expectException(DuplicateResourcePublicationException::class);

        (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('main', 'css/main.css', 'one'),
            $this->request('main', 'css/other.css', 'two'),
        ]);
    }

    public function test_exact_target_collision_is_rejected(): void
    {
        $this->expectException(ResourcePublicationTargetCollisionException::class);

        (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('one', 'assets/app.js', 'one'),
            $this->request('two', 'assets/app.js', 'two'),
        ]);
    }

    public function test_case_only_target_collision_is_rejected_portably(): void
    {
        $this->expectException(ResourcePublicationTargetCollisionException::class);

        (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('one', 'Assets/App.js', 'one'),
            $this->request('two', 'assets/app.js', 'two'),
        ]);
    }

    public function test_manifest_is_sorted_and_queryable_by_target(): void
    {
        $plan = (new DeterministicResourcePublicationPlanner())->compile([
            $this->request('b', 'b.js', 'b'),
            $this->request('a', 'a.js', 'a'),
        ]);

        $manifest = $plan->manifest();
        self::assertSame(2, $manifest->count());
        self::assertTrue($manifest->hasTarget(new ResourcePath('A.JS')));
        self::assertSame('application:a', $manifest->entryForTarget(new ResourcePath('a.js'))?->canonicalData()['qualified_identifier']);
    }

    public function test_manifest_fingerprint_is_reproducible(): void
    {
        $planner = new DeterministicResourcePublicationPlanner();
        $first = $planner->compile([
            $this->request('b', 'b.js', 'b'),
            $this->request('a', 'a.js', 'a'),
        ]);
        $second = $planner->compile([
            $this->request('b', 'b.js', 'b'),
            $this->request('a', 'a.js', 'a'),
        ]);

        self::assertSame($first->manifest()->canonicalJson(), $second->manifest()->canonicalJson());
        self::assertSame($first->manifest()->fingerprint(), $second->manifest()->fingerprint());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $first->manifest()->fingerprint());
    }

    public function test_manifest_fingerprint_changes_when_content_changes(): void
    {
        $planner = new DeterministicResourcePublicationPlanner();
        $first = $planner->compile([$this->request('main', 'main.js', 'one')]);
        $second = $planner->compile([$this->request('main', 'main.js', 'two')]);

        self::assertNotSame($first->manifest()->fingerprint(), $second->manifest()->fingerprint());
    }

    public function test_invalid_content_fingerprint_is_rejected(): void
    {
        $this->expectException(InvalidResourceContentFingerprintException::class);
        new ResourceContentFingerprint('not-a-sha256');
    }

    public function test_negative_content_size_is_rejected(): void
    {
        $this->expectException(InvalidResourcePublicationRequestException::class);

        new ResourcePublicationRequest(
            $this->descriptor('main'),
            new ResourceRootIdentifier('public'),
            new ResourcePath('main.js'),
            ResourceContentFingerprint::fromContent('main'),
            -1,
        );
    }

    public function test_negative_publication_order_is_rejected(): void
    {
        $this->expectException(InvalidResourcePublicationOrderException::class);
        new PlannedResourcePublication($this->request('main', 'main.js', 'main'), -1);
    }

    private function request(string $identifier, string $target, string $content): ResourcePublicationRequest
    {
        return new ResourcePublicationRequest(
            $this->descriptor($identifier),
            new ResourceRootIdentifier('public'),
            new ResourcePath($target),
            ResourceContentFingerprint::fromContent($content),
            strlen($content),
        );
    }

    private function descriptor(string $identifier): ResourceDescriptor
    {
        return new ResourceDescriptor(
            new ResourceIdentifier($identifier),
            new ResourceNamespace('application'),
            new ResourceType('script'),
            new ResourcePath('source/' . $identifier . '.js'),
            new ResourcePriority(),
        );
    }
}
