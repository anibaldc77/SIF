<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\MetadataPolicy;

use Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyApplicatorInterface;

final readonly class DefaultOpenIdFederationMetadataPolicyApplicator implements OpenIdFederationMetadataPolicyApplicatorInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function apply(
        array $metadata,
        string $entityType,
        OpenIdFederationMetadataPolicy $policy
    ): OpenIdFederationMetadataPolicyApplicationResult {
        $resolved = $metadata;
        $violations = [];

        foreach ($policy->forEntityType($entityType) as $parameter => $parameterPolicy) {
            $present = array_key_exists($parameter, $resolved);
            $value = $present ? $resolved[$parameter] : null;

            if ($parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::Value)) {
                $value = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::Value);

                if ($value === null) {
                    unset($resolved[$parameter]);
                    $present = false;
                } else {
                    $resolved[$parameter] = $value;
                    $present = true;
                }
            }

            if ($parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::Add)) {
                $add = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::Add);

                if (!is_array($add)) {
                    $violations[] = $parameter . ':add_invalid';
                } else {
                    $current = $present && is_array($value) ? array_values($value) : [];
                    $resolved[$parameter] = array_values(array_unique(array_merge($current, array_values($add)), SORT_REGULAR));
                    $value = $resolved[$parameter];
                    $present = true;
                }
            }

            if (
                !$present
                && $parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::Default)
            ) {
                $value = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::Default);
                $resolved[$parameter] = $value;
                $present = true;
            }

            if (
                $present
                && $parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::OneOf)
            ) {
                $allowed = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::OneOf);

                if (!is_array($allowed) || !in_array($value, $allowed, true)) {
                    $violations[] = $parameter . ':one_of_failed';
                }
            }

            if (
                $present
                && $parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::SubsetOf)
            ) {
                $allowed = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::SubsetOf);

                if (!is_array($allowed) || !is_array($value)) {
                    $violations[] = $parameter . ':subset_of_invalid';
                } else {
                    $resolved[$parameter] = array_values(array_intersect($value, $allowed));
                    $value = $resolved[$parameter];
                }
            }

            if (
                $present
                && $parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::SupersetOf)
            ) {
                $required = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::SupersetOf);

                if (!is_array($required) || !is_array($value)) {
                    $violations[] = $parameter . ':superset_of_invalid';
                } else {
                    foreach ($required as $requiredValue) {
                        if (!in_array($requiredValue, $value, true)) {
                            $violations[] = $parameter . ':superset_of_failed';
                            break;
                        }
                    }
                }
            }

            if ($parameterPolicy->has(OpenIdFederationMetadataPolicyOperator::Essential)) {
                $essential = $parameterPolicy->value(OpenIdFederationMetadataPolicyOperator::Essential);

                if (!is_bool($essential)) {
                    $violations[] = $parameter . ':essential_invalid';
                } elseif ($essential && !$present) {
                    $violations[] = $parameter . ':essential_missing';
                }
            }
        }

        return new OpenIdFederationMetadataPolicyApplicationResult(
            $violations === [],
            $resolved,
            $violations
        );
    }
}
