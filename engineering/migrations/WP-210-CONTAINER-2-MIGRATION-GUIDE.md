---
id: WP-210-CONTAINER-2-MIGRATION-GUIDE
title: Container 2.0 Migration Guide
summary: Provides a staged, non-breaking migration path from string-based service access toward Container 2.0 definitions, identifiers, constructor injection, scopes, validation, and compilation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-210
tags:
  - container
  - migration
  - compatibility
depends_on:
  - EG-256
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# Container 2.0 Migration Guide

## Goal

Adopt Container 2.0 without replacing existing framework bootstrap behavior in one step.

## Stage 1 â€” Explicit composition

Create Container 2.0 at an infrastructure composition boundary:

```php
$composition = (new ContainerCompositionFactory())->create();
```

## Stage 2 â€” Register definitions

Register new services explicitly:

```php
$composition->definitions()->register(
    ServiceDefinition::forAutowiredClass(
        new ServiceIdentifier(Mailer::class),
        Mailer::class,
    ),
);
```

## Stage 3 â€” Bridge string callers

Use the compatibility adapter:

```php
$legacy = $composition->compatibility();
$mailer = $legacy->get(Mailer::class);
```

The adapter is transitional.

## Stage 4 â€” Prefer constructor injection

Move service lookup out of domain and application services.

Keep container access at composition and infrastructure boundaries.

## Stage 5 â€” Introduce scopes explicitly

```php
$scope = $composition
    ->compatibility()
    ->beginScope('command-import');
```

Always close scopes at the owning boundary.

## Stage 6 â€” Validate and compile descriptions

Run validation before application startup:

```php
$report = $composition->validator()->validate();
```

Generate a deterministic fingerprint for deployment diagnostics:

```php
$fingerprint = $composition->compiler()->compile()->fingerprint();
```

## Stage 7 â€” Framework integration

Do not modify `Framework::create()` ad hoc.

A future governed increment must define:

- application ownership;
- bootstrap injection;
- service-provider registration order;
- legacy mapping;
- deprecation schedule;
- rollback strategy.

## Compatibility rules

During migration:

- do not maintain separate singleton caches;
- do not copy definitions between containers at runtime;
- do not hide the container in a mutable global;
- do not resolve scoped services from the root;
- do not bypass validation for compiled deployments.

