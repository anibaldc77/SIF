<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;
use Throwable;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderCredentialRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityOperationEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionRevokerInterface;
use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;

final readonly class FederatedRevocationOrchestrator
{
    public function __construct(
        private FederatedSessionRevokerInterface $sessionRevoker,
        private FederatedProviderCredentialRevokerInterface $providerCredentialRevoker,
        private FederatedIdentityLinkRevokerInterface $identityLinkRevoker,
        private FederatedSecurityOperationEventPublisherInterface $eventPublisher
    ) {
    }

    public function execute(
        FederatedRevocationRequest $request,
        DateTimeImmutable $now
    ): FederatedRevocationExecution {
        $steps = [];

        $this->eventPublisher->publish(
            new FederatedSecurityOperationEvent(
                'federation.revocation.started',
                $now,
                [
                    'identity_id' => $request->localIdentityId()->value(),
                    'scope' => $request->scope()->value,
                    'reason' => $request->reason()->code(),
                ]
            )
        );

        foreach ($this->plannedSteps($request->scope()) as $step) {
            $result = $this->executeStep($request, $step);
            $steps[] = $result;

            $this->eventPublisher->publish(
                new FederatedSecurityOperationEvent(
                    $result->succeeded()
                        ? 'federation.revocation.step_succeeded'
                        : 'federation.revocation.step_failed',
                    $now,
                    [
                        'identity_id' => $request->localIdentityId()->value(),
                        'step' => $step->value,
                        'failure_type' => $result->failureType(),
                    ]
                )
            );

            if (!$result->succeeded()) {
                break;
            }
        }

        $execution = new FederatedRevocationExecution(
            $request,
            $steps
        );

        $this->eventPublisher->publish(
            new FederatedSecurityOperationEvent(
                $execution->succeeded()
                    ? 'federation.revocation.completed'
                    : 'federation.revocation.incomplete',
                $now,
                [
                    'identity_id' => $request->localIdentityId()->value(),
                    'scope' => $request->scope()->value,
                ]
            )
        );

        return $execution;
    }

    /**
     * @return list<FederatedRevocationStep>
     */
    private function plannedSteps(
        FederatedRevocationScope $scope
    ): array {
        return match ($scope) {
            FederatedRevocationScope::LocalSessions => [
                FederatedRevocationStep::LocalSessions,
            ],
            FederatedRevocationScope::ProviderCredentials => [
                FederatedRevocationStep::ProviderCredentials,
            ],
            FederatedRevocationScope::IdentityLink => [
                FederatedRevocationStep::IdentityLink,
            ],
            FederatedRevocationScope::All => [
                FederatedRevocationStep::LocalSessions,
                FederatedRevocationStep::ProviderCredentials,
                FederatedRevocationStep::IdentityLink,
            ],
        };
    }

    private function executeStep(
        FederatedRevocationRequest $request,
        FederatedRevocationStep $step
    ): FederatedRevocationStepResult {
        try {
            $succeeded = match ($step) {
                FederatedRevocationStep::LocalSessions => $this->revokeLocalSessions($request),
                FederatedRevocationStep::ProviderCredentials => $this->revokeProviderCredentials($request),
                FederatedRevocationStep::IdentityLink => $this->revokeIdentityLink($request),
            };

            return new FederatedRevocationStepResult(
                $step,
                true,
                $succeeded,
                $succeeded ? null : 'operation_rejected'
            );
        } catch (Throwable $exception) {
            return new FederatedRevocationStepResult(
                $step,
                true,
                false,
                $exception::class
            );
        }
    }

    private function revokeLocalSessions(
        FederatedRevocationRequest $request
    ): bool {
        $this->sessionRevoker->revokeAll(
            $request->localIdentityId(),
            $request->reason()
        );

        return true;
    }

    private function revokeProviderCredentials(
        FederatedRevocationRequest $request
    ): bool {
        $this->providerCredentialRevoker->revoke(
            $request->federatedIdentity(),
            $request->reason()
        );

        return true;
    }

    private function revokeIdentityLink(
        FederatedRevocationRequest $request
    ): bool {
        return $this->identityLinkRevoker->revoke(
            $request->federatedIdentity(),
            $request->reason()
        );
    }
}
