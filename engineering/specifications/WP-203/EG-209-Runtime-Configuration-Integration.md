---
id: EG-209
title: Runtime Configuration Integration
summary: Defines application-level configuration ownership, bootstrap source loading, provider access, capability exposure, and deterministic freezing after successful Runtime boot.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - foundation
  - configuration
  - runtime
  - bootstrap
  - providers
  - lifecycle
work_package: WP-203
depends_on:
  - EG-207
  - EG-208
  - ADR-0005
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-209 — Runtime Configuration Integration

## 1. Purpose

WP-203-I3 integrates the configuration core and configuration loaders with the Foundation Runtime while preserving the existing `ApplicationInterface` contract.

The increment establishes application ownership of one isolated configuration repository, deterministic source loading during bootstrap, typed provider access, capability discovery, and irreversible freezing after a successful boot.

## 2. Compatibility model

`ApplicationInterface` SHALL remain unchanged.

`ConfigurationAwareApplicationInterface` SHALL extend `CapabilityAwareApplicationInterface` and SHALL expose:

```php
public function configuration(): MutableConfigurationInterface;
```

`Application` SHALL implement `ConfigurationAwareApplicationInterface`.

Existing consumers typed only against `ApplicationInterface` SHALL continue to operate without modification.

## 3. Configuration ownership

Each `Application` instance SHALL own exactly one configuration repository.

The repository SHALL NOT be shared implicitly between application instances.

When no repository is injected, `Application` SHALL create an empty `ConfigurationRepository`.

The application SHALL expose the `configuration` capability through its existing capability registry.

## 4. Bootstrap source loading

`Bootstrap` SHALL accept an optional `ConfigurationFileLoader` and an ordered iterable of source paths.

Sources SHALL be processed from lowest to highest precedence using the semantics defined by EG-208.

`Bootstrap::createApplication()` SHALL load and merge all configured sources before constructing `Application`.

An empty source list SHALL produce an empty mutable configuration repository.

Source-loading failures SHALL propagate before an application instance is returned. They SHALL NOT be converted into provider lifecycle failures.

The no-argument `Bootstrap` constructor SHALL remain valid and SHALL use the default PHP and JSON loaders.

## 5. Provider access

Service providers MAY test whether the received application implements `ConfigurationAwareApplicationInterface`.

Configuration SHALL remain mutable throughout provider `register()` and `boot()` execution.

Providers MAY read existing values and add or replace values during those phases.

The base `ServiceProvider` contract SHALL remain unchanged.

## 6. Freezing policy

After every provider has completed `boot()` successfully, `Lifecycle` SHALL freeze the application configuration before returning a successful `BootResult`.

A frozen configuration SHALL remain readable.

Mutation after successful boot SHALL produce `FrozenConfigurationException` according to EG-207.

If provider registration, capability discovery, or provider boot fails, configuration SHALL NOT be frozen. This preserves diagnostic and recovery workflows before the application has reached a successful boot boundary.

Repeated freezing SHALL remain safe and idempotent.

## 7. Capability integration

The default capability set SHALL include:

- `runtime`;
- `foundation`;
- `providers`;
- `lifecycle`;
- `configuration`.

The capability registry SHALL remain the single source of truth for the legacy string capability facade and typed capability resolution.

## 8. Isolation and determinism

Applications created independently SHALL receive independent configuration repositories.

Source order SHALL be deterministic.

Provider execution order SHALL remain unchanged.

Configuration freezing SHALL occur only after the final provider boot hook succeeds.

## 9. Excluded scope

WP-203-I3 SHALL NOT introduce:

- `.env` parsing;
- direct environment-variable expansion;
- environment-specific filename conventions;
- schema validation;
- configuration caching;
- dynamic runtime reload;
- module-level configuration discovery.

Those concerns belong to later increments.

## 10. Acceptance criteria

The increment is accepted when:

1. `ApplicationInterface` remains unchanged.
2. `Application` exposes one typed configuration repository.
3. default applications contain an empty mutable repository before boot.
4. `Bootstrap` loads ordered PHP and JSON sources.
5. providers can read and mutate configuration during `register()` and `boot()`.
6. configuration freezes after successful boot.
7. failed boot does not freeze configuration.
8. configuration remains readable after freezing.
9. applications retain isolated repositories.
10. existing capability integration tests are aligned with the new default capability.
11. PHPUnit, PHPStan, Composer validation, and SIF Builder governance checks pass.
