<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use PHPUnit\Framework\TestCase;
final class GovernanceRemediationPlanningExpirationAndAuditBoundariesTest extends TestCase{
public function testContractsExist():void{
foreach([
\Sif\Foundation\Security\Contracts\RemediationPlanRepositoryInterface::class,
\Sif\Foundation\Security\Contracts\GovernanceExpirationProcessorInterface::class,
\Sif\Foundation\Security\Contracts\GovernanceEventPublisherInterface::class,
\Sif\Foundation\Security\Contracts\GovernanceRemediationPlannerInterface::class,
] as $c){self::assertTrue(interface_exists($c));}}
}
