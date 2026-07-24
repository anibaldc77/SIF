---
id: EG-206
title: Runtime Capability Integration
summary: Defines the integration of the typed capability registry with the Application facade and provider boot lifecycle while preserving existing public contracts and nominal capability behavior.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - capability
  - application
  - service-provider
  - lifecycle
  - compatibility
work_package: WP-202
depends_on:
  - EG-204
  - EG-205
  - ADR-0005
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-206 — Runtime Capability Integration

## 1. Purpose

WP-202-I2 integrates the typed capability registry with the existing application capability facade while preserving all public behavior established by the capability-driven Runtime architecture.

The increment establishes one capability source of truth per application and allows service providers to declare typed capabilities during the boot lifecycle without extending existing mandatory provider or application contracts.

## 2. Architectural decisions

1. `ApplicationInterface` SHALL remain unchanged.
2. `CapabilityAwareApplicationInterface` SHALL extend the existing application contract with typed capability registration and resolution.
3. `Application` SHALL own exactly one `CapabilityRegistry`.
4. `capabilities()`, `hasCapability()`, and `addCapability()` SHALL use that registry as their backing store.
5. `NamedCapability` SHALL represent nominal capabilities exposed through the legacy string facade.
6. Typed capability identifiers SHALL already use the canonical lowercase Foundation identifier form.
7. Providers MAY implement `CapabilityProviderInterface` to declare capabilities.
8. Capability discovery SHALL execute after every provider `register()` hook and before any provider `boot()` hook.
9. Duplicate or invalid provider capabilities SHALL fail boot using `capability.registration_failed`.
10. Capability registries SHALL remain isolated between application instances.

## 3. Boot ordering

```text
provider.register*
capability.discovery*
provider.boot*
```

This ordering permits provider registration hooks to prepare capability instances while guaranteeing that all discovered capabilities are visible during provider boot.

Capability discovery SHALL NOT change the authority model defined by WP-201. `Kernel` remains the lifecycle authority, `Lifecycle` remains responsible for deterministic provider execution, and `Application` remains the owner of application-scoped capability state.

## 4. Compatibility requirements

Applications created through `Framework::create()` SHALL retain:

- their default nominal capabilities;
- deterministic capability order;
- lowercase identifier normalization for the nominal API;
- surrounding whitespace removal;
- silent deduplication through `addCapability()`;
- independent registries per application instance.

Existing implementations of `ApplicationInterface` SHALL acquire no additional mandatory methods.

Existing implementations of `ServiceProviderInterface` SHALL acquire no additional mandatory methods. Capability declaration remains opt-in through `CapabilityProviderInterface`.

## 5. Failure behavior

When capability discovery encounters an invalid or duplicate typed capability, the lifecycle result SHALL contain an error with code:

```text
capability.registration_failed
```

The `Kernel` SHALL apply the existing failed-boot behavior and transition the Runtime to `Failed` according to the Runtime transition model.

The original throwable SHALL remain available as the failure cause through the existing boot error model.

## 6. Acceptance criteria

- `Application` owns one `CapabilityRegistry`.
- The nominal and typed APIs share the same registry.
- Default nominal capability behavior remains backward compatible.
- Provider-declared capabilities are visible before provider `boot()` execution.
- Invalid and duplicate provider capabilities fail deterministically.
- Application instances do not share capability state.
- `ApplicationInterface` remains unchanged.
- `ServiceProviderInterface` remains unchanged.
- PHPUnit, PHPStan, Composer validation, and Builder repository validation pass.
